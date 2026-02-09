<?php
/**
 * @package     BR Simple Contact
 * @author      Janderson Moreira
 * @copyright   Copyright (C) 2026 Janderson Moreira
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

// Importante: O Helper precisa ser carregado manualmente agora
require_once __DIR__ . '/src/Helper/SimpleContactHelper.php';

class PlgContentBr_Simple_Contact extends CMSPlugin
{
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        $this->loadLanguage();
        
        if (strpos($article->text, '{simplecontact}') === false) {
            return;
        }

        $app = Factory::getApplication();
        
        // Instancia o Helper (agora sem namespace complexo)
        $helper = new BrSimpleContactHelper($this->params);

        // Lógica AJAX
        if ($app->input->get('br_contact_submit', 0, 'int') === 1 && $app->input->get('via_ajax', 0, 'int') === 1) {
            
            $result = $helper->handleSubmission();

            if (ob_get_length()) { ob_end_clean(); }
            header('Content-Type: application/json');
            
            if ($result === null) {
                echo json_encode(['success' => false, 'message' => 'No data']);
            } else {
                echo json_encode($result);
            }
            
            $app->close();
        }

        // Renderiza Template
        ob_start();
        $uniqueId = rand(1000, 9999);
        $pluginParams = $this->params;
        include __DIR__ . '/tmpl/default.php';
        $formHtml = ob_get_clean();

        $article->text = str_replace('{simplecontact}', $formHtml, $article->text);
    }
}