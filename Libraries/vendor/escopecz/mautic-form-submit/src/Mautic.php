<?php

namespace Escopecz\MauticFormSubmit;

use Escopecz\MauticFormSubmit\Mautic\Form;
use Escopecz\MauticFormSubmit\Mautic\Contact;
use Escopecz\MauticFormSubmit\Mautic\Cookie as MauticCookie;

/**
 * Mautic representation
 */
class Mautic
{
    /**
     * Mautic base (root) URL
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Mautic Contact
     *
     * @var Contact
     */
    protected $contact;

    /**
     * Mautic Contact Cookie
     *
     * @var MauticCookie
     */
    protected $cookie;

    /**
     * Constructor
     *
     * @param string $baseUrl
     */
    public function __construct($baseUrl)
    {
        $this->baseUrl = rtrim(trim($baseUrl), '/');
        $this->cookie = new MauticCookie;
        $this->contact = new Contact($this->cookie);
    }

    /**
     * Returns Mautic's base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Returns new Mautic Form representation object
     *
     * @param  int $id
     *
     * @return Form
     */
    public function getForm($id)
    {
        return new Form($this, $id);
    }

    /**
     * Sets the Mautic Contact if you want to replace the default one
     *
     * @param Contact $contact
     *
     * @return Mautic
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Returns Mautic Contact representation object
     *
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Returns Mautic Cookie representation object
     *
     * @return MauticCookie
     */
    public function getCookie()
    {
        return $this->cookie;
    }
}
