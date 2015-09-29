<?php

namespace Lead\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LeadAttributeValue
 *
 * @ORM\Table(name="lead_attribute_values", indexes={@ORM\Index(name="idx_lead_id", columns={"lead_id"}), @ORM\Index(name="idx_attribute_id", columns={"attribute_id"})})
 * @ORM\Entity
 */
class LeadAttributeValue
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", length=65535, nullable=true)
     */
    private $value;

    /**
     * @var \Lead\Entity\LeadAttribute
     *
     * @ORM\ManyToOne(targetEntity="Lead\Entity\LeadAttribute", inversedBy="values", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     * })
     */
    private $attribute;

    /**
     * @var \Lead\Entity\Lead
     *
     * @ORM\ManyToOne(targetEntity="Lead\Entity\Lead", inversedBy="attributes", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lead_id", referencedColumnName="id")
     * })
     */
    private $lead;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return LeadAttributeValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set attribute
     *
     * @param \Lead\Entity\LeadAttribute $attribute
     *
     * @return LeadAttributeValue
     */
    public function setAttribute(\Lead\Entity\LeadAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return \Lead\Entity\LeadAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set lead
     *
     * @param \Lead\Entity\Lead $lead
     *
     * @return LeadAttributeValue
     */
    public function setLead(\Lead\Entity\Lead $lead = null)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * Get lead
     *
     * @return \Lead\Entity\Lead
     */
    public function getLead()
    {
        return $this->lead;
    }
}
