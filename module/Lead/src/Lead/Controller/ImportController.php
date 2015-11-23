<?php
namespace Lead\Controller;

use Zend\File\Transfer\Adapter\Http as FileHttp;
use Zend\Validator\File\Size;
use Zend\Validator\File\Extension as FileExt;
use Application\Controller\AbstractCrudController;
use Lead\Form\ImportForm;
use Lead\Entity\Lead;
use Lead\Entity\LeadAttributeValue;
use Lead\Entity\LeadAttribute;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Application\Hydrator\Strategy\DateTimeStrategy;
use Account\Entity\Account;
use Event\Entity\Event;
use Account\Utility\IdGenerator;
use Zend\Form\Form;
use User\Provider\IdentityAwareTrait;
use Zend\Validator\File\MimeType;
use Zend\Form\Fieldset;
use Application\Utility\Helper;

/**
 *
 * @author arstropica
 *        
 */
class ImportController extends AbstractCrudController
{
    
    use IdentityAwareTrait;

    protected $successImportMessage = 'The Lead(s) were successfully imported.';

    protected $errorImportMessage = 'There was a problem importing your Leads.';

    protected $errorImportTypeMessage = 'There was a problem with the file you were attempting to import.';

    /**
     *
     * @var array
     */
    protected $results;

    /**
     *
     * @var int
     */
    protected $stage = 1;

    /**
     *
     * @var integer
     */
    protected $batchSize = 20;

    /**
     *
     * @var array
     */
    protected $data;

    public function __construct()
    {}

    public function importAction()
    {
        $sl = $this->getServiceLocator();
        $view = $sl->get('viewhelpermanager');
        
        $view->get('HeadLink')->appendStylesheet($view->get('basePath')
            ->__invoke('css/nav-wizard.bootstrap.css'));
        
        $view->get('HeadScript')->appendFile($view->get('basePath')
            ->__invoke('js/typeahead.bundle.js'));
        
        // Stage 1
        $this->init();
        // Get Form
        $form = $this->getImportForm();
        $request = $this->getRequest();
        $post = $request->getPost()->toArray();
        
        $result = $this->manageStages($post, $form, false);
        return $result ?  : $this->rollBack();
    }

    /**
     * Kick-off stage handling
     *
     * @param array $post            
     * @param ImportForm $form            
     * @param boolean $reload            
     *
     * @return array|boolean
     */
    protected function manageStages($post, $form, $reload = false)
    {
        if ($post) {
            try {
                // Stage 2
                // Handle Uploads
                if ($this->handleUpload($post, $form, $reload)) {
                    return $this->results;
                }
                
                // Stage 3
                // Handle Field Matching
                if ($this->handleMatch($post, $form, $reload)) {
                    return $this->results;
                }
                
                // Stage 4
                // Handle Confirm
                if ($this->handleConfirm($post, $form, $reload)) {
                    return $this->results;
                }
                
                // Stage 5
                // Handle Save
                if ($this->handleImport($post, $form)) {
                    return $this->redirect()->toRoute($this->getActionRoute('list'), [], true);
                }
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        } else {
            try {
                $this->setImportCache($this->stage);
            } catch (\Exception $e) {
                // ...
            }
            $form->addUploadField();
            $this->results['form'] = $form;
            return $this->results;
        }
        return false;
    }

    /**
     * Initialize Results (Stage 1)
     *
     * @param integer $stage            
     * @return void
     */
    protected function init($stage = null)
    {
        $stage = $stage ?  : $this->stage;
        $results = $this->results = array(
            'fields' => false,
            'stage' => $stage,
            'headings' => [],
            'form' => $this->getImportForm(),
            '_tmp' => false,
            'dataCount' => false,
            'data' => false,
            'valid' => false
        );
        return $results;
    }

    /**
     * Handle Uploads (Stage 2)
     *
     * @param array $post            
     * @param ImportForm $form            
     * @param boolean $reload            
     * @return boolean
     */
    protected function handleUpload($post, $form, $reload = false)
    {
        $outcome = false;
        $request = $this->getRequest();
        $files = $request->getFiles()->toArray();
        $data = array_merge_recursive($post, $request->getFiles()->toArray());
        
        // set data post and file ...
        $form->setData($data);
        
        if ($form->isValid()) {
            // Handle Uploads
            if ($files) {
                $file = isset($post['leadsUpload']) ? $post['leadsUpload'] : $this->params()->fromFiles('leadsUpload');
                $tmp_file = $this->validateImportFile($file);
                if ($tmp_file) {
                    $extract = $this->parseFile($this->getUploadPath() . '/' . $tmp_file);
                    if ($extract && $extract['count']) {
                        // Setup Import Form
                        $fieldSet = $form->addImportFieldset(array_combine($extract['headings'], $extract['headings']), $this->isAdmin());
                        $this->setTypeAhead('Company', "Account\\Entity\\Account", "name");
                        $form->get('leadTmpFile')->setValue($tmp_file);
                        $form->get('submit')->setValue('Import');
                        
                        $outcome = true;
                        $this->stage = 2;
                        $this->results['stage'] = $this->stage;
                        $this->results['fields'] = $form->getLeadAttributes();
                        $this->results['_tmp'] = $tmp_file;
                        $this->results['count'] = $extract['count'];
                        $this->results['headings'] = $extract['headings'];
                        $this->results['form'] = $form;
                        $this->setImportCache($this->stage, $data);
                    } else {
                        $message = "There seems to be a problem with reading your CSV file or it is invalid.";
                        $this->flashMessenger()->addErrorMessage($message);
                    }
                }
            }
        } else {
            $message = "There seems to be a problem with reading your CSV file or it is invalid.";
            $this->flashMessenger()->addErrorMessage($message);
            $messages = $form->getMessages();
            if ($messages) {
                $this->flashMessenger()->addErrorMessage($this->formatFormMessages($form));
            }
        }
        return $outcome;
    }

    /**
     * Handle Field Matching and Validation (Stage 3)
     *
     * @param array $post            
     * @param Form $form            
     * @param boolean $reload            
     */
    protected function handleMatch($post, $form, $reload = false)
    {
        $outcome = false;
        $valid = false;
        $request = $this->getRequest();
        
        $form->setData($post);
        
        if ($form->isValid()) {
            if ($post && isset($post['match'], $post['leadTmpFile']) && ! isset($post['duplicate'])) {
                $match = $post['match'];
                if (Helper::is_json($match)) {
                    $match = json_decode($match, true);
                }
                $company = $post['Company'];
                $tmp_file = $this->getUploadPath() . '/' . $post['leadTmpFile'];
                
                $importFieldset = $form->getImportFieldset();
                $csv = $this->parseFile($tmp_file);
                if ($csv['count']) {
                    $data = $this->mapImportedValues($csv['body'], $match, false);
                    $valid = $this->validateImportData($data);
                    
                    if ($valid) {
                        $invalid = array_filter($valid);
                        if ($invalid) {
                            $outcome = true;
                            $this->stage = 3;
                            $form = $this->addDuplicateFields($form, $invalid);
                            $form = $this->addMatchFields($form, $match);
                            $form->get('submit')->setValue('Validate');
                            $form->addReviewField();
                            $form->addCancelField();
                            $form->addHiddenField('Company', $company);
                            $headings = $this->getHeadings(null, [
                                'Similarity' => 'score'
                            ]);
                            $this->results['stage'] = $this->stage;
                            $this->results['fields'] = $form->getLeadAttributes();
                            $this->results['data'] = $data;
                            $this->results['valid'] = $valid;
                            $this->results['form'] = $form;
                            $this->results['headings'] = $headings;
                            $this->setImportCache($this->stage, $post);
                        }
                    }
                }
                if (! $valid) {
                    $outcome = false;
                    $message = "No valid records could be imported.";
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        } else {
            $message = "You have invalid Form Entries.";
            $this->flashMessenger()->addErrorMessage($message);
            $messages = $form->getMessages();
            if ($messages) {
                $this->flashMessenger()->addErrorMessage($this->formatFormMessages($form));
            }
        }
        return $outcome;
    }

    /**
     * Handle Confirm (Stage 4)
     *
     * @param array $post            
     * @param Form $form            
     * @param boolean $reload            
     *
     * @return mixed
     */
    protected function handleConfirm($post, $form, $reload = false)
    {
        $outcome = false;
        $valid = false;
        $request = $this->getRequest();
        $invalid = false;
        
        $form->setData($post);
        
        if ($form->isValid()) {
            if ($post && isset($post['match'], $post['leadTmpFile'])) {
                $match = $post['match'];
                if (Helper::is_json($match)) {
                    $match = json_decode($match, true);
                }
                $company = $post['Company'];
                $tmp_file = $this->getUploadPath() . '/' . $post['leadTmpFile'];
                $duplicates = isset($post['duplicate']) ? $post['duplicate'] : false;
                
                $importFieldset = $form->getImportFieldset();
                $form->addConfirmField();
                $csv = $this->parseFile($tmp_file);
                if ($csv['count']) {
                    $data = $this->mapImportedValues($csv['body'], $match, false);
                    $valid = $this->validateImportData($data, $duplicates);
                    
                    if ($valid) {
                        $outcome = true;
                        $headings = $this->getHeadings();
                        $invalid = array_filter($valid);
                        $form = $this->addImportFields($form, $data, $valid);
                        $form->get('submit')->setValue('Confirm');
                        $form->addCancelField();
                        $form->addHiddenField('Company', $company);
                        $this->stage = 4;
                        $this->results['stage'] = $this->stage;
                        $this->results['fields'] = $form->getLeadAttributes();
                        $this->results['data'] = $data;
                        $this->results['valid'] = $valid;
                        $this->results['form'] = $form;
                        $this->results['headings'] = $headings;
                        $this->setImportCache($this->stage, $post);
                    }
                }
                if (! $valid || $valid === $invalid) {
                    $outcome = false;
                    $message = "No valid records could be imported.";
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        } else {
            $message = "You have invalid Form Entries.";
            $this->flashMessenger()->addErrorMessage($message);
            $messages = $form->getMessages();
            if ($messages) {
                $this->flashMessenger()->addErrorMessage($this->formatFormMessages($form));
            }
        }
        return $outcome;
    }

    /**
     * Handle Import & Save (Stage 5)
     *
     * @param array $post            
     * @param Form $form            
     */
    protected function handleImport($post, $form)
    {
        $outcome = true;
        $request = $this->getRequest();
        
        $fields = $form->getLeadAttributes();
        $this->results['fields'] = $fields;
        
        $form->setData($post);
        
        if ($form->isValid()) {
            if (isset($post['leads'])) {
                $accountName = empty($post['Company']) ? false : $post['Company'];
                $account_id = false;
                if ($accountName) {
                    $account = $accountName ? $this->findAccount($accountName) : false;
                    if (! $account) {
                        $account = new Account();
                        $guid = IdGenerator::generate();
                        $account->setName($accountName)->setDescription($accountName);
                        $account->setGuid($guid);
                        try {
                            $em = $this->getEntityManager();
                            $em->persist($account);
                            $em->flush();
                            $account_id = $account->getId();
                        } catch (\Exception $e) {
                            $account = false;
                            $outcome = false;
                            $this->flashMessenger()->addErrorMessage($e->getMessage());
                        }
                    } else {
                        $account_id = $account->getId();
                    }
                } else {
                    $account = false;
                }
                
                $all_leads_json = $post['leads'];
                $all_leads = @\Zend\Json\Json::decode($all_leads_json, \Zend\Json\Json::TYPE_ARRAY);
                $leads = isset($all_leads['valid']) ? $all_leads['valid'] : false;
                $company = $post['Company'];
                if ($leads && is_array($leads)) {
                    $leads = array_values($leads);
                    try {
                        $this->createServiceEvent()
                            ->setEntityClass($this->getEntityClass())
                            ->setDescription("Lead Import");
                        $em = $this->getEntityManager();
                        for ($i = 1; $i <= count($leads); ++ $i) {
                            $extract = isset($leads[$i - 1]) ? $leads[$i - 1] : false;
                            if ($extract) {
                                $lead = null;
                                $this->createServiceEvent()
                                    ->setEntityClass($this->getEntityClass())
                                    ->setDescription("Lead Import");
                                $entity = new Lead();
                                $lead = $this->hydrate($entity, $extract);
                                if ($lead) {
                                    try {
                                        $em = $this->getEntityManager();
                                        $em->persist($lead);
                                        $em->flush();
                                        $lead_id = $lead->getId();
                                        if ($account_id && $lead instanceof Lead) {
                                            $account = $this->findAccount($account_id, 'id');
                                            $account->addLead($lead);
                                        }
                                        $message = 'Lead #' . $lead_id . ' was imported.';
                                        $this->getServiceEvent()
                                            ->setEntityId($lead_id)
                                            ->setMessage($message);
                                        $this->logEvent("ImportAction.post");
                                        $em->flush();
                                        $em->clear();
                                    } catch (\Exception $e) {
                                        $this->logError($e);
                                        if ($outcome) {
                                            $this->flashMessenger()->addErrorMessage($e->getMessage());
                                        }
                                        $outcome = false;
                                        $em->clear();
                                    }
                                } elseif ($outcome) {
                                    $outcome = false;
                                }
                            }
                        }
                        $em->flush();
                        $em->clear();
                    } catch (\Exception $e) {
                        $this->logError($e);
                        if ($outcome) {
                            $this->flashMessenger()->addErrorMessage($e->getMessage());
                        }
                        $outcome = false;
                    }
                }
                if (! $outcome) {
                    $message = "One or more records could not be properly imported.";
                    $this->flashMessenger()->addErrorMessage($message);
                } else {
                    $message = count($leads) . " record(s) were imported.";
                    $this->flashMessenger()->addSuccessMessage($message);
                    $this->stage = 5;
                }
            } else {
                $outcome = false;
            }
        } else {
            $outcome = false;
        }
        
        return $outcome;
    }

    /**
     * Rollback to earlier stage
     *
     * @param string $stage            
     * @return Ambigous <\Zend\Http\Response, boolean>
     */
    protected function rollBack($stage = null)
    {
        // disable state caching
        return $this->getRedirect();
        $result = false;
        $form = $this->getImportForm();
        $data = $this->getImportCache($stage);
        $this->stage = $this->getImportCache($stage, 'stage');
        if ($data && $this->stage > 1) {
            $result = $this->manageStages($data, $form, true);
        }
        return $result ?  : $this->getRedirect();
    }

    /**
     * Cache form data for back navigation
     *
     * @param unknown $stage            
     * @param string $data            
     */
    protected function setImportCache($stage, $data = null)
    {
        $sessionImportCache = $this->getSession('import');
        $sessionImportCache->stage = $stage;
        if ($stage == 1 || ! isset($sessionImportCache->data)) {
            $sessionImportCache->data = [];
        }
        $sessionImportCache->data[$stage] = $data;
    }

    /**
     * Get form stage/cache from previous stage
     *
     * @param string $stage            
     * @param string $mode            
     * @return NULL|array
     */
    protected function getImportCache($stage = null, $mode = 'data')
    {
        $output = null;
        $sessionImportCache = $this->getSession('import');
        if (isset($sessionImportCache, $sessionImportCache->data, $sessionImportCache->stage)) {
            $stage = $stage ?  : $sessionImportCache->stage;
            if (isset($sessionImportCache->data[$stage])) {
                $output = $mode == 'data' ? $sessionImportCache->data[$stage] : $stage;
            } elseif ($mode != 'data') {
                $output = 1;
            }
        }
        return $output;
    }

    /**
     * Validate Import Data
     *
     * @param array $data            
     */
    protected function validateImportData($data, $overrides = false)
    {
        $valid = false;
        $required = $this->checkRequiredFields($data);
        
        if (! $required) {
            $message = "One or more required fields were missing.";
            $this->flashMessenger()->addErrorMessage($message);
        } else {
            
            if ($data) {
                $this->data = $data;
                $valid = array_map(function ($result, $index) use($overrides) {
                    if ($overrides && isset($overrides[$index]) && $overrides[$index]) {
                        return false;
                    } else {
                        return isset($result['outcome']) && $result['outcome'] ? $result : false;
                    }
                }, array_map(array(
                    $this,
                    'checkDuplicateImport'
                ), $data, array_keys($data)), array_keys($data));
                $invalid = array_filter($valid);
                if (count($invalid) && ! $overrides) {
                    $this->flashMessenger()->addErrorMessage(count($invalid) . " potential duplicate leads were found in your imported data.");
                }
            }
        }
        return $valid;
    }

    /**
     *
     * @param array $data            
     * @return \Lead\Form\ImportForm
     */
    protected function getImportForm($data = [])
    {
        $sl = $this->getServiceLocator();
        $form = $sl->get('FormElementManager')->get('Lead\Form\ImportForm');
        if ($data) {
            $form->setData($data);
            if (! $form->isValid()) {
                $form->setData(array());
            }
        }
        $form->get('submit')->setValue('Upload');
        return $form;
    }

    protected function setTypeAhead($fieldName, $entityClass, $property)
    {
        $sl = $this->getServiceLocator();
        $view = $sl->get('viewhelpermanager');
        $script = $view->get('inlineScript');
        
        $format = <<<JTPL
	        var data = %2\$s;
			var substringMatcher = function(strs) {
			  return function findMatches(q, cb) {
			    var matches, substringRegex;
		
			    // an array that will be populated with substring matches
			    matches = [];
		
			    // regex used to determine if a string contains the substring `q`
			    substrRegex = new RegExp(q, 'i');
		
			    // iterate through the pool of strings and for any string that
			    // contains the substring `q`, add it to the `matches` array
			    $.each(strs, function(i, str) {
			      if (substrRegex.test(str)) {
			        matches.push(str);
			      }
			    });
		
			    cb(matches);
			  };
			};
			$('INPUT[name=%1\$s]').typeahead({
			  hint: true,
			  highlight: true,
			  minLength: 2
			},
			{
			  name: 'data',
			  source: substringMatcher(data)
			});
JTPL;
        
        $em = $this->getEntityManager();
        $objRepository = $em->getRepository($entityClass);
        
        $data = $objRepository->getArrayAccounts($property);
        $inlinejs = sprintf($format, $fieldName, json_encode($data));
        $script->appendScript($inlinejs, 'text/javascript', array(
            'noescape' => true
        )); // Disable CDATA comments
    }

    protected function validateImportFile($file)
    {
        $size = new Size(array(
            'min' => 128,
            'max' => 20480000
        )); // min/max bytes filesize
        
        $ext = new FileExt(array(
            'extension' => array(
                'xls',
                'xlsx',
                'csv'
            )
        ));
        
        $mime = new MimeType(array(
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'text/plain',
            'text/csv'
        ));
        
        $adapter = new FileHttp();
        $adapter->setValidators(array(
            $size,
            $ext,
            $mime
        ), $file['name']);
        $isValid = $adapter->isValid();
        if (! $isValid) {
            $dataError = $adapter->getMessages();
            $this->flashMessenger()->addErrorMessage($this->errorImportTypeMessage . '<br>' . implode(' <br>', $dataError));
        } else {
            $adapter->setDestination($this->getUploadPath());
            if ($adapter->receive($file['name'])) {
                $isValid = $file['name'];
            }
        }
        return $isValid;
    }

    protected function getHeadings($fields = null, $extra = [])
    {
        if (! $fields) {
            $fields = [
                'firstname' => 'First Name',
                'lastname' => 'Last Name',
                'email' => 'Email'
            ];
        }
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->add('select', 'a')
            ->add('from', '\Lead\Entity\LeadAttribute a')
            ->groupBy('a.attributeDesc')
            ->orderBy('a.attributeOrder');
        foreach ($fields as $key => $value) {
            $qb->orWhere("a.attributeDesc = :{$key}");
            $qb->setParameter($key, $value);
        }
        $results = $qb->getQuery()->getResult();
        $headings = [];
        $headings['Time Created'] = "timecreated";
        foreach ($results as $result) {
            $headings[$result->getAttributeDesc()] = $result->getId();
        }
        return array_merge($headings, $extra);
    }

    protected function getUploadPath()
    {
        $config = $this->getServiceLocator()->get('config');
        return $config['upload_location'];
    }

    protected function parseFile($file)
    {
        $rows = [];
        $result = false;
        try {
            $objPHPExcel = \PHPExcel_IOFactory::load($file);
            $rows = $objPHPExcel->getActiveSheet()->toArray();
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return false;
        }
        if ($rows && count($rows) > 1) {
            $field_count = count($rows[1]);
            $result = [
                'count' => 0,
                'headings' => [],
                'body' => []
            ];
            $field_count = 0;
            $i = 0;
            foreach ($rows as &$_row) {
                while (null === end($_row)) {
                    array_pop($_row);
                    if ($i > 0) {
                        if (count($_row) === $field_count) {
                            break 1;
                        }
                    }
                }
                if ($field_count < count($_row)) {
                    $field_count = count($_row);
                }
                $i ++;
            }
            $headings = array_shift($rows);
            if ($headings !== array_filter($headings) || count($headings) !== $field_count) {
                $message = "There seems to be a problem with your file. One or more headings are missing.";
                $this->flashMessenger()->addErrorMessage($message);
                return false;
            }
            $csv = [];
            $i = 0;
            $row_count = count($rows);
            foreach ($rows as $row) {
                if (count($headings) == count($row)) {
                    $csv[] = array_combine($headings, $row);
                    $i ++;
                }
            }
            if ($row_count !== $i) {
                $error_message = ($row_count - $i) . " of " . $row_count . " records could not be imported.";
                $this->flashMessenger()->addErrorMessage($error_message);
            }
            $result['headings'] = $headings;
            $result['count'] = $i;
            $result['body'] = $csv;
        } else {
            $error_message = "No records could not be imported.";
            $this->flashMessenger()->addErrorMessage($error_message);
            return false;
        }
        return $result;
    }

    protected function addDuplicateFields(ImportForm $form, $records)
    {
        $duplicates = [];
        if ($records) {
            $duplicateFieldset = new Fieldset('duplicate');
            foreach ($records as $i => $record) {
                if (isset($record['outcome'], $record['record']) && $record['outcome']) {
                    $duplicates[$i] = $record['record'];
                    $duplicateFieldset->add(array(
                        'name' => $i,
                        'type' => 'Zend\Form\Element\Checkbox',
                        'options' => array(
                            'label' => 'Duplicate',
                            'label_attributes' => array(
                                'class' => array(
                                    'sr-only'
                                )
                            ),
                            'use_hidden_element' => true,
                            'checked_value' => '1',
                            'unchecked_value' => '0'
                        ),
                        'attributes' => array(
                            'data-on-text' => 'Yes',
                            'data-off-text' => 'No',
                            'data-off-color' => 'warning',
                            'data-size' => 'small',
                            'class' => 'review-switch'
                        )
                    ));
                }
            }
            $form->add($duplicateFieldset);
            $form->add(array(
                'name' => "duplicates",
                'attributes' => array(
                    'value' => \Zend\Json\Json::encode($duplicates, true, array(
                        'silenceCyclicalExceptions' => true
                    )),
                    'type' => 'hidden'
                )
            ));
        }
        return $form;
    }

    protected function addImportFields(ImportForm $form, $data, $valid = false)
    {
        $import = [];
        if ($data) {
            foreach ($data as $i => $row) {
                if (! $valid) {
                    $is_valid = 'valid';
                } elseif (isset($valid[$i]['outcome']) && $valid[$i]['outcome']) {
                    $is_valid = 'invalid';
                } else {
                    $is_valid = 'valid';
                }
                foreach ($row as $field => $fieldSet) {
                    foreach ($fieldSet as $fieldName => $value) {
                        if (is_array($value)) {
                            foreach ($value as $_fieldName => $_value) {
                                $import[$is_valid][$i][$field][$fieldName][$_fieldName] = $_value;
                            }
                        } else {
                            $import[$is_valid][$i][$field][$fieldName] = $value;
                        }
                    }
                }
            }
            $form->add(array(
                'name' => "leads",
                'attributes' => array(
                    'value' => \Zend\Json\Json::encode($import, true, array(
                        'silenceCyclicalExceptions' => true
                    )),
                    'type' => 'hidden'
                )
            ));
        }
        return $form;
    }

    protected function addMatchFields(ImportForm $form, $match)
    {
        if ($match) {
            $form->add(array(
                'name' => "match",
                'attributes' => array(
                    'value' => \Zend\Json\Json::encode($match, true, array(
                        'silenceCyclicalExceptions' => true
                    )),
                    'type' => 'hidden'
                )
            ));
        }
        return $form;
    }

    protected function mapImportedValues($csv, $match, $structured = true)
    {
        $leads = array();
        $i = 0;
        foreach ($csv as $row) {
            $mappedArray = $this->mapCSVRow($row, $match);
            if ($structured) {
                $lead = new Lead();
                $leads[$i] = $this->extract($this->hydrate($lead, $mappedArray));
            } else {
                $leads[$i] = $mappedArray;
            }
            $i ++;
        }
        
        return $leads;
    }

    protected function checkDuplicateImport($row, $index)
    {
        $result = [
            'score' => 0,
            'fields' => [],
            'record' => null,
            'duplicate' => null,
            'outcome' => false
        ];
        $em = $this->getEntityManager();
        $leadRepository = $em->getRepository("Lead\\Entity\\Lead");
        $attributes = false;
        $lead = false;
        
        // Get Key Attributes
        $attributeRepository = $em->getRepository("Lead\\Entity\\LeadAttribute");
        $criteria = $attributeRepository->findBy(array(
            'attributeDesc' => array(
                'First Name',
                'Last Name',
                'City',
                'State',
                'Zip Code',
                'Phone',
                'Email'
            )
        ));
        
        // Get Key Attribute IDs
        $fields = array();
        if ($criteria) {
            /* @var $attribute \Lead\Entity\LeadAttribute */
            foreach ($criteria as $attribute) {
                $fields[$attribute->getAttributeDesc()] = $attribute->getId();
            }
        }
        
        $where = [];
        
        // Build DQL conditions
        foreach ($fields as $fieldName => $field) {
            if (isset($row[$field]) && is_array($row[$field])) {
                $value = current($row[$field]);
                switch ($fieldName) {
                    case 'First Name':
                    case 'Last Name':
                        $where['and'][$field] = $value;
                        break;
                    default:
                        $where['or'][$field] = $value;
                        break;
                }
            }
        }
        // Execute Query
        if (isset($where['and'])) {
            /* @var $lead \Lead\Entity\Lead */
            $lead = $leadRepository->findLeadBy($where);
        }
        
        // Get key values by filtering records
        $key_values = array_map(function ($v) {
            return is_array($v) ? current($v) : $v;
        }, array_filter($row, function ($v) use($fields) {
            $k = key($v);
            return in_array($k, array_keys($fields));
        }));
        
        if ($key_values) {
            if ($lead) {
                $attributeValues = $lead->getAttributes(false);
                if ($attributeValues) {
                    $attributes = [
                        'timecreated' => $lead->getTimecreated(),
                        'ipaddress' => $lead->getIpaddress(),
                        'referrer' => $lead->getReferrer()
                    ];
                    foreach ($attributeValues as $attributeValue) {
                        $attribute = $attributeValue->getAttribute();
                        if ($attribute) {
                            $attributes[$attribute->getId()] = $attributeValue->getValue();
                        }
                    }
                    
                    // Filter record by duplicate key values
                    $result = $this->calculateSimilarity([
                        $attributes
                    ], $key_values, $row);
                }
            } else {
                if (isset($this->data[$index])) {
                    $this->data[$index] = null;
                }
                $data = $this->data;
                if ($data) {
                    // Find potential duplicate values and filter them by key values
                    $result = $this->calculateSimilarity($data, $key_values, $row);
                }
            }
        }
        
        return $result;
    }

    protected function calculateSimilarity($data, $key_values, $row = [])
    {
        $result = [];
        $objRepository = $this->getEntityManager()->getRepository('\Lead\Entity\LeadAttribute');
        
        // Assign scores to records based on the number and type of matching key values
        $similarities = array_map(function ($record) use($key_values, $objRepository) {
            $score = 0;
            $fields = [];
            foreach ($key_values as $attribute_id => $value) {
                $desc = $objRepository->getDescFromID($attribute_id);
                $record_value = null;
                if (isset($record[$attribute_id])) {
                    if (is_array($record[$attribute_id])) {
                        $record_value = current($record[$attribute_id]);
                    } else {
                        $record_value = $record[$attribute_id];
                    }
                }
                switch ($desc) {
                    case 'First Name':
                    case 'Last Name':
                        if (isset($record_value) && ($record_value == $value) && ! empty($value)) {
                            $score += 25;
                            $fields[$attribute_id] = $value;
                        }
                        break;
                    default:
                        if (isset($record_value) && ($record_value == $value) && ! empty($value)) {
                            $score += 10;
                            $fields[$attribute_id] = $value;
                        }
                        break;
                }
            }
            return [
                'score' => $score,
                'fields' => $fields,
                'duplicate' => $record
            ];
        }, $data);
        
        // Filter out records with a similarity score < 50
        if ($similarities) {
            $similarities = array_filter($similarities, function ($record) {
                return $record['score'] > 50;
            });
        }
        
        // Select record with highest score
        if ($similarities) {
            usort($similarities, function ($a, $b) {
                return $a['score'] - $b['score'];
            });
            
            $result = end($similarities);
            $result['record'] = $row;
            $result['outcome'] = true;
        }
        
        return $result;
    }

    protected function mapCSVRow($row, $match)
    {
        $result = array();
        foreach ($match as $fieldName => $fieldSet) {
            $attributeId = $fieldSet['importField'];
            if ($attributeId && isset($row[$fieldName])) {
                switch ($attributeId) {
                    case 'Question':
                        $result[$attributeId][$fieldName] = [
                            $fieldName => $row[$fieldName]
                        ];
                        break;
                    default:
                        $result[$attributeId] = [
                            $fieldName => $row[$fieldName]
                        ];
                        break;
                }
            }
        }
        return $result;
    }

    protected function hydrate(Lead $lead, $data = [])
    {
        $em = $this->getEntityManager();
        $leadAttributeRepository = $em->getRepository("Lead\\Entity\\LeadAttribute");
        
        foreach ($data as $attributeId => $csvRowItem) {
            if (is_array($csvRowItem)) {
                $csvHeading = key($csvRowItem);
                $csvValue = current($csvRowItem);
                if (is_numeric($attributeId)) {
                    $leadAttribute = $leadAttributeRepository->findOneBy([
                        'id' => $attributeId
                    ]);
                    if ($leadAttribute) {
                        $leadAttributeValue = new LeadAttributeValue();
                        
                        $leadAttributeValue->setValue($csvValue);
                        $leadAttributeValue->setAttribute($leadAttribute);
                        
                        $lead->addAttribute($leadAttributeValue);
                    }
                } elseif ($attributeId == "Question") {
                    if (is_array($csvValue)) {
                        foreach ($csvRowItem as $csvHeading => $arrayValue) {
                            $csvValue = current($arrayValue);
                            
                            $leadAttribute = $leadAttributeRepository->findOneBy([
                                'attributeDesc' => $csvHeading
                            ]);
                            if (! $leadAttribute) {
                                $leadAttribute = new LeadAttribute();
                                $leadAttribute->setAttributeName($attributeId);
                                $leadAttribute->setAttributeDesc($csvHeading);
                            }
                            $leadAttributeValue = new LeadAttributeValue();
                            $leadAttributeValue->setValue($csvValue);
                            $leadAttributeValue->setAttribute($leadAttribute);
                            
                            $lead->addAttribute($leadAttributeValue);
                        }
                    } else {
                        
                        $leadAttribute = $leadAttributeRepository->findOneBy([
                            'attributeDesc' => $csvHeading
                        ]);
                        if (! $leadAttribute) {
                            $leadAttribute = new LeadAttribute();
                            $leadAttribute->setAttributeName($attributeId);
                            $leadAttribute->setAttributeDesc($csvHeading);
                        }
                        $leadAttributeValue = new LeadAttributeValue();
                        $leadAttributeValue->setValue($csvValue);
                        $leadAttributeValue->setAttribute($leadAttribute);
                        
                        $lead->addAttribute($leadAttributeValue);
                    }
                } elseif (in_array($attributeId, [
                    'timecreated',
                    'referrer',
                    'ipaddress'
                ])) {
                    if ($attributeId == 'timecreated') {
                        $isvalid = $this->validateDate($csvValue);
                        $csvValue = $isvalid ? new \DateTime($csvValue) : new \DateTime("now");
                    }
                    $lead->{'set' . ucfirst($attributeId)}($csvValue);
                }
            }
        }
        
        return $lead;
    }

    protected function extract(Lead $lead)
    {
        $entityClass = get_class($lead);
        $hydrator = new DoctrineHydrator($this->getEntityManager(), $entityClass);
        $hydrator->addStrategy('timecreated', new DateTimeStrategy());
        
        return $hydrator->extract($lead);
    }

    protected function findAccount($value, $field = 'name')
    {
        $em = $this->getEntityManager();
        $accountRepository = $em->getRepository("Account\\Entity\\Account");
        
        return $accountRepository->findOneBy([
            $field => $value
        ]);
    }

    protected function checkRequiredFields($data, $required_fields = [
				'timecreated',
				'referrer',
				'ipaddress',
		])
    {
        $matched_fields = is_array($data) && is_array(current($data)) ? array_keys(current($data)) : [];
        $valid = true;
        foreach ($required_fields as $required_field) {
            if (! in_array($required_field, $matched_fields)) {
                $valid = false;
            }
        }
        return $valid;
    }

    private function validateDate($date)
    {
        $stamp = strtotime($date);
        if (! is_numeric($stamp))
            return false;
        $month = date('m', $stamp);
        $day = date('d', $stamp);
        $year = date('Y', $stamp);
        if (checkdate($month, $day, $year))
            return true;
        return false;
    }

    protected function logEvent($event)
    {
        $this->getEventManager()->trigger($event, $this->getServiceEvent());
    }

    protected function logError(\Exception $e, $result = [])
    {
        $this->getServiceEvent()->setIsError(true);
        $this->getServiceEvent()->setMessage($e->getMessage());
        if ($result) {
            $this->getServiceEvent()->setResult(print_r($result, true));
        } else {
            $this->getServiceEvent()->setResult($e->getTraceAsString());
        }
        $this->logEvent('RuntimeError');
    }
}

?>