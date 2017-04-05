<?php

namespace GuestUserTest\Controller;

use GuestUserTest\Controller\GuestUserControllerTestCase;

class ForgotPasswordControllerTest extends GuestUserControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->logout();
    }

    public function tearDown()
    {
        $entityManager = $this->getEntityManager();
        $passwordCreation = $entityManager
            ->getRepository('Omeka\Entity\PasswordCreation')
            ->findOneBy(['user' => $this->testUser->getEntity()]);
        if ($passwordCreation) {
            $entityManager->remove($passwordCreation);
            $entityManager->flush();
        }
        parent::tearDown();
    }

    /**
     * @test
     */
    public function forgotPasswordShouldDisplayEmailSent()
    {
        $csrf = new \Zend\Form\Element\Csrf('csrf');
        $this->postDispatch('/s/test/guestuser/forgot-password', [
            'email' => "test@test.fr",
            'csrf' => $csrf->getValue(),
        ]);

        $this->assertXPathQueryContentContains('//li[@class="success"]', 'Check your email for instructions on how to reset your password');
    }

    /**
     * @test
     */
    public function forgotPasswordShouldSendEmail()
    {
        $csrf = new \Zend\Form\Element\Csrf('csrf');
        $this->postDispatch('/s/test/guestuser/forgot-password', [
            'email' => "test@test.fr",
            'csrf' => $csrf->getValue(),
        ]);

        $mailer = $this->getServiceLocator()->get('Omeka\Mailer');
        $message = $mailer->getMessage();
        $this->assertNotNull($message);

        $body = $message->getBody();
        $this->assertContains('To reset your password, click this link', $body);
    }

}
