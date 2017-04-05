<?php
namespace Omeka\Form;

use DateTimeZone;
use Zend\Form\Form;

class InstallationForm extends Form
{
    public function init()
    {
        // By removing CSRF protection we're removing the need to use session
        // data during installation. This is needed for databse session storage.
        $this->remove('csrf');

        $this->add([
            'name' => 'user',
            'type' => 'fieldset',
            'options' => [
                'label' => 'Create the first user', // @translate
            ],
        ]);
        $this->add([
            'name' => 'settings',
            'type' => 'fieldset',
             'options' => [
                'label' => 'Settings', // @translate
            ],
       ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Submit', // @translate
            ],
        ]);

        $this->get('user')->add([
            'name' => 'email',
            'type' => 'Email',
            'options' => [
                'label' => 'Email', // @translate
            ],
            'attributes' => [
                'id' => 'email',
                'required' => true,
            ],
        ]);
        $this->get('user')->add([
            'name' => 'email-confirm',
            'type' => 'Email',
            'options' => [
                'label' => 'Confirm Email', // @translate
            ],
            'attributes' => [
                'id' => 'email-confirm',
                'required' => true,
            ],
        ]);
        $this->get('user')->add([
            'name' => 'password',
            'type' => 'Password',
            'options' => [
                'label' => 'Password', // @translate
            ],
            'attributes' => [
                'id' => 'password',
                'required' => true,
            ],
        ]);
        $this->get('user')->add([
            'name' => 'password-confirm',
            'type' => 'Password',
            'options' => [
                'label' => 'Confirm Password', // @translate
            ],
            'attributes' => [
                'id' => 'password-confirm',
                'required' => true,
            ],
        ]);
        $this->get('user')->add([
            'name' => 'name',
            'type' => 'Text',
            'options' => [
                'label' => 'Display Name', // @translate
            ],
            'attributes' => [
                'id' => 'name',
                'required' => true,
            ],
        ]);
        $this->get('settings')->add([
            'name' => 'installation_title',
            'type' => 'Text',
            'options' => [
                'label' => 'Installation Title', // @translate
            ],
            'attributes' => [
                'id' => 'installation-title',
                'required' => true,
            ],
        ]);

        $timeZones = DateTimeZone::listIdentifiers();
        $timeZones = array_combine($timeZones, $timeZones);
        $defaultTimeZone = ini_get('date.timezone');
        if (!$defaultTimeZone) {
            $defaultTimeZone = 'UTC';
        }
        $this->get('settings')->add([
            'name' => 'time_zone',
            'type' => 'Select',
            'options' => [
                'label' => 'Time Zone', // @translate
                'value_options' => $timeZones,
            ],
            'attributes' => [
                'id' => 'time-zone',
                'required' => true,
                'value' => $defaultTimeZone,
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->get('user')->add([
            'name' => 'password',
            'required' => true,
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 6,
                    ],
                ],
            ],
        ]);
        $inputFilter->get('user')->add([
            'name' => 'password-confirm',
            'required' => true,
            'validators' => [
                [
                    'name' => 'Identical',
                    'options' => [
                        'token' => 'password',
                        'message' => 'The passwords did not match', // @translate
                    ],
                ],
            ],
        ]);
        $inputFilter->get('user')->add([
            'name' => 'email-confirm',
            'required' => true,
            'validators' => [
                [
                    'name' => 'Identical',
                    'options' => [
                        'token' => 'email',
                        'message' => 'The emails did not match', // @translate
                    ],
                ],
            ],
        ]);
    }
}
