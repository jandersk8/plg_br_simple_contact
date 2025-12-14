<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  content.br_simple_contact
 *
 * @copyright   Copyright (c) 2025 Janderson Moreira. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// SEM NAMESPACE AQUI

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

// Renomeei a classe para garantir que seja única
class BrSimpleContactHelper
{
    protected $params;

    public function __construct(Registry $params)
    {
        $this->params = $params;
    }

    public function handleSubmission()
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        if (!Session::checkToken()) {
            return ['success' => false, 'message' => Text::_('JINVALID_TOKEN')];
        }

        $data = [
            'name'    => $input->getString('br_name', ''),
            'email'   => $input->getString('br_email', ''),
            'message' => $input->getString('br_message', '')
        ];

        if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
            return ['success' => false, 'message' => Text::_('PLG_CONTENT_BR_SIMPLE_CONTACT_ERROR_EMPTY_FIELDS')];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => Text::_('PLG_CONTENT_BR_SIMPLE_CONTACT_ERROR_INVALID_EMAIL')];
        }

        if ($this->sendEmail($data)) {
            $msg = $this->params->get('success_message', Text::_('PLG_CONTENT_BR_SIMPLE_CONTACT_SUCCESS_DEFAULT'));
            return ['success' => true, 'message' => $msg];
        } else {
            return ['success' => false, 'message' => Text::_('PLG_CONTENT_BR_SIMPLE_CONTACT_ERROR_SENDING')];
        }
    }

    protected function sendEmail(array $data): bool
    {
        $mailer = Factory::getMailer();
        $config = Factory::getConfig();

        $sender = [$config->get('mailfrom'), $data['name']];
        $mailer->setSender($sender);

        $recipient = $this->params->get('recipient_email');
        if (empty($recipient)) $recipient = $config->get('mailfrom');
        $mailer->addRecipient($recipient);

        $subject = $this->params->get('email_subject', 'CONTATOS PELO SITE!');
        $mailer->setSubject($subject . ' - ' . $data['name']);

        $body  = "Nome: " . $data['name'] . "\r\n";
        $body .= "E-mail: " . $data['email'] . "\r\n";
        $body .= "Mensagem:\r\n" . $data['message'] . "\r\n";
        
        $mailer->addReplyTo($data['email'], $data['name']);
        $mailer->setBody($body);

        try {
            return $mailer->send();
        } catch (\Exception $e) {
            return false;
        }
    }
}