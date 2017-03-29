<?php

namespace MindbazBundle\Model;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class Subscriber
{
    const STATUS_SUBSCRIBED = 0;
    const STATUS_UNSUBSCRIBED = 1;
    const STATUS_MANUALLY_UNSUBSCRIBED = 2;
    const STATUS_DRAINAGE_INVALID_DOMAIN = 3;
    const STATUS_DRAINAGE_INVALID_SYNTAX = 4;
    const STATUS_DRAINAGE_REPELLERS_LIST = 5;
    const STATUS_DRAINAGE_DUPLICATE = 6;
    const STATUS_DRAINAGE_NLLAI = 7;
    const STATUS_WAITING_REGISTRATION_CONFIRMATION = 8;
    const STATUS_SPAM = 9;
    const STATUS_WAITING_VALIDATION = 10;
    const STATUS_TEST_FAI = 11;
    const STATUS_UNSUBSCRIBED_GROUP = 12;

    const CIVILITY_MR = 0;
    const CIVILITY_MRS = 1;
    const CIVILITY_MLLE = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var \DateTime
     */
    private $firstSubscriptionDate;

    /**
     * @var \DateTime
     */
    private $lastSubscriptionDate;

    /**
     * @var \DateTime
     */
    private $unsubscriptionDate;

    /**
     * @var string
     */
    private $status = self::STATUS_SUBSCRIBED;

    /**
     * @var string
     */
    private $civility = self::CIVILITY_MR;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $zipcode;

    /**
     * @var string
     */
    private $country;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Subscriber
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Subscriber
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFirstSubscriptionDate()
    {
        return $this->firstSubscriptionDate;
    }

    /**
     * @param \DateTime $firstSubscriptionDate
     *
     * @return Subscriber
     */
    public function setFirstSubscriptionDate($firstSubscriptionDate)
    {
        $this->firstSubscriptionDate = $firstSubscriptionDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastSubscriptionDate()
    {
        return $this->lastSubscriptionDate;
    }

    /**
     * @param \DateTime $lastSubscriptionDate
     *
     * @return Subscriber
     */
    public function setLastSubscriptionDate($lastSubscriptionDate)
    {
        $this->lastSubscriptionDate = $lastSubscriptionDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUnsubscriptionDate()
    {
        return $this->unsubscriptionDate;
    }

    /**
     * @param \DateTime $unsubscriptionDate
     *
     * @return Subscriber
     */
    public function setUnsubscriptionDate($unsubscriptionDate)
    {
        $this->unsubscriptionDate = $unsubscriptionDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Subscriber
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getCivility()
    {
        return $this->civility;
    }

    /**
     * @param string $civility
     *
     * @return Subscriber
     */
    public function setCivility($civility)
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return Subscriber
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return Subscriber
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Subscriber
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     *
     * @return Subscriber
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return Subscriber
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }
}
