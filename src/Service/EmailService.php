<?php

namespace App\Service;

use App\Repository\EmailsToSendRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Twig\Environment;

class EmailService
{
    private $systemConfigService;
    private $emailsToSendRepository;
    private $emailConfig;
    private $destinationEmail;
    private $destinationName;
    private $subject;
    private $twig;
    private $body;
    private $email;


    public function __construct(EmailsToSendRepository $emailsToSendRepository, SystemConfigService $systemConfigService, Environment $twig)
    {
        $this->emailsToSendRepository = $emailsToSendRepository;
        $this->systemConfigService = $systemConfigService;
        $this->twig = $twig;
        $this->email  = new PHPMailer(true);
        $this->emailConfig = $this->systemConfigService->getSystemConfig();
    }

    public function setEmailInfo() {
        $this->email->Host = $this->emailConfig->getEmailhost();
        $this->email->Username = $this->emailConfig->getEmailusername();
        $this->email->Password = $this->emailConfig->getEmailpassword();
        $this->email->CharSet = "UTF-8";
        $this->email->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
        $this->email->isSMTP();                                            //Send using SMTP
        $this->email->SMTPAuth = true;                                   //Enable SMTP authentication
        $this->email->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $this->email->Port = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->email->setFrom($this->emailConfig->getEmailusername(), $this->emailConfig->getCompanyName());                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->email->addReplyTo($this->emailConfig->getEmailusername(), 'Dúvidas');                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $this->email->isHTML(true);                                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    }

    public function body($emailInfo, $template)
    {
//        dd($emailInfo);
        $this->body = $this->twig->render("emails/$template.html.twig", [
            'emailInfo' => $emailInfo,
            'companyInfo' => $this->emailConfig
        ]);

    }

    public function setCostumerInfo($costumerInfo){
        $this->destinationEmail = $costumerInfo['destinationEmail'];
        $this->destinationName = $costumerInfo['destinationName'];
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function sendEmail()
    {

        $this->email->addAddress($this->destinationEmail, $this->destinationName);
        $this->email->Subject = $this->subject;
        $this->email->Body = $this->body;
        $this->email->send();
    }
    public function saveInDataBase()
    {
        $this->emailsToSendRepository->newEmailToSend($this->body, $this->subject, $this->destinationEmail, $this->destinationName);
    }

    public function sendEmailsInDataBase()
    {
        $emailsToSend = $this->emailsToSendRepository->findBy(['status' => 0]);

        foreach ($emailsToSend as $emailToSend) {
            $this->updateEmailStatus($emailToSend->getId());
        }

        $this->setEmailInfo();

        $idsToDelete = [];

        foreach ($emailsToSend as $emailToSend) {
            $this->body = $emailToSend->getBody();
            $this->subject = $emailToSend->getSubject();
            $this->setCostumerInfo(['destinationEmail' => $emailToSend->getDestinationEmail(), 'destinationName' => $emailToSend->getDestinationName()]);
            $this->sendEmail();
            $idsToDelete[] = $emailToSend->getId();
        }
//
//        foreach ($idsToDelete as $idToDelete) {
//            $this->emailsToSendRepository->deleteEmail($idToDelete);
//        }

    }

    public function updateEmailStatus($emailId)
    {
        $this->emailsToSendRepository->updateEmailStatus($emailId);
    }

}