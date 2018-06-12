<?php
declare (strict_types = 1);

namespace App\Database\Entities\MailChimp;

use Doctrine\ORM\Mapping as ORM;
use EoneoPay\Utils\Str;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @ORM\Entity()
 */
class MailChimpListMember extends MailChimpEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @var string
     */
    private $memberId;

    /**
     * @ORM\Column(name="list_id", type="string")
     *
     * @var string
     */
    private $listId;

    /**
     * @ORM\Column(name="mail_chimp_id", type="string", nullable=true)
     *
     * @var string
     */
    private $mailChimpId;

    /**
     * @ORM\Column(name="email_address", type="string")
     *
     * @var string
     */
    private $emailAddress;

    /**
     * @ORM\Column(name="status", type="string")
     *
     * @var string
     */
    private $status;
   
    /**
     * @ORM\Column(name="merge_fields", type="array")
     *
     * @var array
     */
    private $mergeFields;

    /**
     * Get id.
     *
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->memberId;
    }
    
    /**
     * Get id.
     *
     * @return null|string
     */
    public function getListId(): ?string
    {
        return $this->listId;
    }

    /**
     * Get mailchimp id of the list.
     *
     * @return null|string
     */
    public function getMailChimpId(): ?string
    {
        return $this->mailChimpId;
    }

    public function getValidationRules(): array
    {
        return [
            'list_id' => 'required',
            'email_address' => 'required',
            'status' => 'required|string',
            'mail_chimp_id' => 'nullable|string',
            'merge_fields' => 'nullable|array'
        ];
    }

    /**
     * Set mailchimp id of the list.
     *
     * @param string $mailChimpId
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setMailChimpId(string $mailChimpId): MailChimpListMember
    {
        $this->mailChimpId = $mailChimpId;

        return $this;
    }

    public function setEmailAddress(string $emailAddress) : MailChimpListMember
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }


    public function setStatus(string $status) : MailChimpListMember 
    {
        $this->status = $status;

        return $this;
    }

    public function setListId(string $listId) : MailChimpListMember
    {
        $this->listId = $listId;

        return $this;
    }

    public function setMergeFields(array $mergeFields = null) : MailChimpListMember
    {
        $this->mergeFields = $mergeFields;

        return $this;
    }

    /**
     * Get array representation of entity.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        $str = new Str();

        foreach (\get_object_vars($this) as $property => $value) {
            $array[$str->snake($property)] = $value;
        }
        return $array;
    }
}
