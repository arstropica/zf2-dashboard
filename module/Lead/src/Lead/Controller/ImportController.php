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

/**
 *
 * @author arstropica
 *        
 */
class ImportController extends AbstractCrudController
{

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

	public function __construct ()
	{}

	public function importAction ()
	{
		$sl = $this->getServiceLocator();
		$view = $sl->get('viewhelpermanager');
		
		$view->get('HeadLink')->appendStylesheet(
				$view->get('basePath')
					->__invoke('css/nav-wizard.bootstrap.css'));
		
		$view->get('HeadScript')->appendFile(
				$view->get('basePath')
					->__invoke('js/typeahead.bundle.js'));
		
		// Stage 1
		$this->init();
		// Get Form
		$form = $this->getImportForm();
		$request = $this->getRequest();
		$post = $request->getPost()->toArray();
		
		if ($post) {
			try {
				// Stage 2
				// Handle Uploads
				if ($this->handleUpload($post, $form)) {
					return $this->results;
				}
				
				// Stage 3
				// Handle Field Matching
				if ($this->handleMatch($post, $form)) {
					return $this->results;
				}
				
				// Stage 4
				// Handle Save
				if ($this->handleImport($post, $form)) {
					return $this->redirect()->toRoute(
							$this->getActionRoute('list'), [], true);
				}
			} catch (\Exception $e) {
				$this->flashMessenger()->addErrorMessage($e->getMessage());
			}
		} else {
			$this->results['form'] = $form;
			$this->setImportCache($this->stage, $this->results);
			$form->addUploadField();
			return $this->results;
		}
		
		return $this->rollBack();
	}

	/**
	 * Initialize Results (Stage 1)
	 *
	 * @return void
	 */
	protected function init ($stage = null)
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
	 * @param Form $form        	
	 * @param boolean $reload        	
	 * @return boolean
	 */
	protected function handleUpload ($post, $form, $reload = false)
	{
		$outcome = false;
		$request = $this->getRequest();
		$files = $request->getFiles()->toArray();
		$data = array_merge_recursive($post, $request->getFiles()->toArray());
		
		// set data post and file ...
		$form->setData($data);
		
		if ($form->isValid() || $reload) {
			// Handle Uploads
			if ($files) {
				$file = isset($post['leadsUpload']) ? $post['leadsUpload'] : $this->params()->fromFiles(
						'leadsUpload');
				$tmp_file = $this->validateImportFile($file);
				if ($tmp_file) {
					$csv = $this->extractCSV(
							$this->getUploadPath() . '/' . $tmp_file);
					
					if ($csv['count']) {
						// Setup Import Form
						$fieldSet = $form->addImportFieldset(
								array_combine($csv['headings'], 
										$csv['headings']));
						$this->setTypeAhead('Company', 
								"Account\\Entity\\Account", "name");
						$form->get('leadTmpFile')->setValue($tmp_file);
						$form->get('submit')->setValue('Import');
						
						$outcome = true;
						$this->stage = 2;
						$this->results['stage'] = $this->stage;
						$this->results['fields'] = $form->getLeadAttributes();
						$this->results['_tmp'] = $tmp_file;
						$this->results['count'] = $csv['count'];
						$this->results['headings'] = $csv['headings'];
						$this->results['form'] = $form;
						$this->setImportCache($this->stage, $this->results);
					}
				}
			}
		} else {
			$message = "There seems to be a problem with reading your CSV file or it is invalid.";
			$this->flashMessenger()->addErrorMessage($message);
		}
		return $outcome;
	}

	/**
	 * Handle Field Matching (Stage 3)
	 *
	 * @param array $post        	
	 * @param Form $form        	
	 * @param boolean $reload        	
	 */
	protected function handleMatch ($post, $form, $reload = false)
	{
		$outcome = false;
		$valid = false;
		$request = $this->getRequest();
		
		$form->setData($post);
		
		if ($form->isValid() || $reload) {
			if ($post && isset($post['match'], $post['leadTmpFile'])) {
				$match = $post['match'];
				$company = $post['Company'];
				$tmp_file = $this->getUploadPath() . '/' . $post['leadTmpFile'];
				
				$importFieldset = $form->getImportFieldset();
				$form->addConfirmField();
				$csv = $this->extractCSV($tmp_file);
				if ($csv['count']) {
					$data = $this->mapImportedValues($csv['body'], $match, 
							false);
					
					if (($valid = $this->validateImportData($data)) == true) {
						if ($valid) {
							$form = $this->addImportFields($form, $data, $valid);
							$form->get('submit')->setValue('Confirm');
							$form->addHiddenField('Company', $company);
							$headings = array_intersect_key(
									$form->getAttributeFields(), 
									array_flip(
											[
													'Time Created',
													'First Name',
													'Last Name',
													'Email'
											]));
							$outcome = true;
							$this->stage = 3;
							$this->results['stage'] = $this->stage;
							$this->results['fields'] = $form->getLeadAttributes();
							$this->results['data'] = $data;
							$this->results['valid'] = $valid;
							$this->results['form'] = $form;
							$this->results['headings'] = $headings;
							$this->setImportCache($this->stage, $this->results);
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
		}
		return $outcome;
	}

	/**
	 * Handle Import & Save (Stage 4)
	 *
	 * @param array $post        	
	 * @param Form $form        	
	 */
	protected function handleImport ($post, $form)
	{
		$outcome = true;
		$request = $this->getRequest();
		
		$fields = $form->getLeadAttributes();
		$this->results['fields'] = $fields;
		
		$form->setData($post);
		
		if ($form->isValid()) {
			if (isset($post['leads'])) {
				$accountName = empty($post['Company']) ? false : $post['Company'];
				if ($accountName) {
					$account = $accountName ? $this->findAccount($accountName) : false;
					if (! $account) {
						$account = new Account();
						$guid = IdGenerator::generate();
						$account->setName($accountName)->setDescription(
								$accountName);
						$account->setGuid($guid);
						try {
							$em = $this->getEntityManager();
							$em->persist($account);
							$em->flush();
						} catch (\Exception $e) {
							$account = false;
							$outcome = false;
							$this->flashMessenger()->addErrorMessage(
									$e->getMessage());
						}
					}
				} else {
					$account = false;
				}
				
				$leads = $post['leads'];
				$company = $post['Company'];
				if ($leads && is_array($leads)) {
					foreach ($leads as $extract) {
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
								if ($account instanceof Account &&
										 $account->getId() &&
										 $lead instanceof Lead && $lead_id) {
									$account->addLead($lead);
								}
								$message = 'Lead #' . $lead_id . ' was imported.';
								$this->getServiceEvent()
									->setEntityId($lead_id)
									->setMessage($message);
								$this->logEvent("ImportAction.post");
							} catch (\Exception $e) {
								$this->logError($e);
								if ($outcome) {
									$this->flashMessenger()->addErrorMessage(
											$e->getMessage());
								}
								$outcome = false;
							}
						} elseif ($outcome) {
							$outcome = false;
						}
					}
					$em->flush();
					$em->clear();
				}
				if (! $outcome) {
					$message = "One or more records could not be properly imported.";
					$this->flashMessenger()->addErrorMessage($message);
				} else {
					$message = count($leads) . " records were imported.";
					$this->flashMessenger()->addSuccessMessage($message);
					$this->stage = 4;
				}
			} else {
				$outcome = false;
			}
		}
		
		return $outcome;
	}

	/**
	 * Rollback to earlier stage
	 *
	 * @param string $stage        	
	 * @return Ambigous <\Zend\Http\Response, boolean>
	 */
	protected function rollBack ($stage = null)
	{
		$output = false;
		$form = $this->getImportForm();
		$results = $this->getImportCache($stage);
		$this->stage = $this->getImportCache($stage, 'stage');
		return $results ?  : $this->getRedirect();
	}

	/**
	 * Cache form data for back navigation
	 *
	 * @param unknown $stage        	
	 * @param string $data        	
	 */
	protected function setImportCache ($stage, $data = null)
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
	protected function getImportCache ($stage = null, $mode = 'data')
	{
		$output = null;
		$sessionImportCache = $this->getSession('import');
		if (isset($sessionImportCache, $sessionImportCache->data, 
				$sessionImportCache->stage)) {
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
	protected function validateImportData ($data)
	{
		$valid = false;
		$required = $this->checkRequiredFields($data);
		
		if (! $required) {
			$message = "One or more required fields were missing.";
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			
			if ($data) {
				$valid = array_map(
						function  ($v)
						{
							return $v ? 'valid' : 'invalid';
						}, 
						array_map(
								array(
										$this,
										'checkDuplicateImport'
								), $data));
				if (($invalid = array_keys($valid, 'invalid')) == true) {
					$this->flashMessenger()->addErrorMessage(
							count($invalid) .
									 " duplicate leads were found in your imported data.");
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
	protected function getImportForm ($data = [])
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

	protected function setTypeAhead ($fieldName, $entityClass, $property)
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
		$script->appendScript($inlinejs, 'text/javascript', 
				array(
						'noescape' => true
				)); // Disable CDATA comments
	}

	protected function validateImportFile ($file)
	{
		$size = new Size(array(
				'min' => 128,
				'max' => 2048000
		)); // min/max bytes filesize
		
		$adapter = new FileHttp();
		$ext = new FileExt(
				array(
						'extension' => array(
								'csv'
						)
				));
		$adapter->setValidators(array(
				$size,
				$ext
		), $file['name']);
		$isValid = $adapter->isValid();
		if (! $isValid) {
			$dataError = $adapter->getMessages();
			$this->flashMessenger()->addErrorMessage(
					$this->errorImportTypeMessage . '<br>' .
							 implode(' <br>', $dataError));
		} else {
			$adapter->setDestination($this->getUploadPath());
			if ($adapter->receive($file['name'])) {
				$isValid = $file['name'];
			}
		}
		return $isValid;
	}

	protected function getUploadPath ()
	{
		$config = $this->getServiceLocator()->get('config');
		return $config['upload_location'];
	}

	protected function extractCSV ($filepath)
	{
		$result = [
				'body' => false,
				'headings' => false,
				'count' => false
		];
		$csv = $this->csvImport($filepath);
		if ($csv && $csv instanceof \Iterator) {
			$result['body'] = [];
			foreach ($csv as $_csv) {
				$_row = [];
				foreach ($_csv as $k => $v) {
					$_row[$this->clean_bom($k)] = $v;
				}
				$result['body'][] = $_row;
			}
			$result['headings'] = array_keys(current($result['body']));
			$result['count'] = count($result['body']);
		}
		return $result;
	}

	protected function addImportFields (ImportForm $form, $data, $valid = false)
	{
		if ($data) {
			foreach ($data as $i => $row) {
				if (! $valid || (isset($valid[$i]) && $valid[$i] == 'valid')) {
					foreach ($row as $field => $fieldSet) {
						foreach ($fieldSet as $fieldName => $value) {
							if (is_array($value)) {
								foreach ($value as $_fieldName => $_value) {
									$form->add(
											array(
													'name' => "leads[{$i}][{$field}][{$fieldName}][{$_fieldName}]",
													'attributes' => array(
															'value' => $_value,
															'type' => 'hidden'
													)
											));
								}
							} else {
								$form->add(
										array(
												'name' => "leads[{$i}][{$field}][{$fieldName}]",
												'attributes' => array(
														'value' => $value,
														'type' => 'hidden'
												)
										));
							}
						}
					}
				}
			}
		}
		return $form;
	}

	protected function mapImportedValues ($csv, $match, $structured = true)
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

	protected function checkDuplicateImport ($row)
	{
		$result = true;
		$em = $this->getEntityManager();
		$leadRepository = $em->getRepository("Lead\\Entity\\Lead");
		
		$where = [];
		$fields = [
				'ipaddress',
				'timecreated'
		];
		
		foreach ($fields as $field) {
			if (isset($row[$field]) && is_array($row[$field])) {
				$value = current($row[$field]);
				if ($field == 'timecreated' && ! $value instanceof \DateTime) {
					$value = new \DateTime(
							date('Y-m-d H:i:s', strtotime($value)));
				}
				$where[$field] = $value;
			}
		}
		if ($where) {
			$result = ! $leadRepository->findOneBy($where);
		}
		return $result;
	}

	protected function mapCSVRow ($row, $match)
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

	protected function clean_bom ($str)
	{
		return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str), '"');
	}

	protected function hydrate (Lead $lead, $data = [])
	{
		$em = $this->getEntityManager();
		$leadAttributeRepository = $em->getRepository(
				"Lead\\Entity\\LeadAttribute");
		
		foreach ($data as $attributeId => $csvRowItem) {
			if (is_array($csvRowItem)) {
				$csvHeading = key($csvRowItem);
				$csvValue = current($csvRowItem);
				if (is_numeric($attributeId)) {
					$leadAttribute = $leadAttributeRepository->findOneBy(
							[
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
							
							$leadAttribute = $leadAttributeRepository->findOneBy(
									[
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
						
						$leadAttribute = $leadAttributeRepository->findOneBy(
								[
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
				} elseif (in_array($attributeId, 
						[
								'timecreated',
								'referrer',
								'ipaddress'
						])) {
					if ($attributeId == 'timecreated') {
						$isvalid = $this->validateDate($csvValue);
						$csvValue = $isvalid ? new \DateTime($csvValue) : new \DateTime(
								"now");
					}
					$lead->{'set' . ucfirst($attributeId)}($csvValue);
				}
			}
		}
		
		return $lead;
	}

	protected function extract (Lead $lead)
	{
		$entityClass = get_class($lead);
		$hydrator = new DoctrineHydrator($this->getEntityManager(), $entityClass);
		$hydrator->addStrategy('timecreated', new DateTimeStrategy());
		
		return $hydrator->extract($lead);
	}

	protected function findAccount ($accountName)
	{
		$em = $this->getEntityManager();
		$accountRepository = $em->getRepository("Account\\Entity\\Account");
		
		return $accountRepository->findOneBy([
				'name' => $accountName
		]);
	}

	protected function checkRequiredFields ($data, 
			$required_fields = [
				'timecreated',
				'referrer',
				'ipaddress'
		])
	{
		$matched_fields = is_array($data) && is_array(current($data)) ? array_keys(
				current($data)) : [];
		
		$valid = true;
		foreach ($required_fields as $required_field) {
			if (! in_array($required_field, $matched_fields)) {
				$valid = false;
			}
		}
		return $valid;
	}

	private function validateDate ($date)
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

	protected function logEvent ($event)
	{
		$this->getEventManager()->trigger($event, $this->getServiceEvent());
	}

	protected function logError (\Exception $e, $result = [])
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