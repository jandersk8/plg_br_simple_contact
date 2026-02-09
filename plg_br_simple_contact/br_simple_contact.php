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

// O Helper precisa ser carregado manualmente conforme sua estrutura
require_once __DIR__ . '/src/Helper/SimpleContactHelper.php';

/**
 * Plugin de Contato Simples
 * O nome da classe deve seguir o padrão: Plg[Grupo][Elemento]
 * Para o elemento 'br_simple_contact', o ideal é Br_simple_contact
 */
class PlgContentBr_simple_contact extends CMSPlugin
{
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        // Verifica se a tag está presente no texto do artigo
        if (strpos($article->text, '{simplecontact}') === false) {
            return;
        }

        // Carrega o arquivo de idioma do plugin
        $this->loadLanguage();

        $app = Factory::getApplication();
        
        // Instancia o Helper passando os parâmetros do plugin
        $helper = new BrSimpleContactHelper($this->params);

        // Lógica de processamento AJAX (Submissão do formulário)
        if ($app->input->get('br_contact_submit', 0, 'int') === 1 && $app->input->get('via_ajax', 0, 'int') === 1) {
            
            $result = $helper->handleSubmission();

            // Limpa qualquer saída anterior para garantir um JSON limpo
            if (ob_get_length()) { 
                ob_end_clean(); 
            }
            
            header('Content-Type: application/json');
            
            if ($result === null) {
                echo json_encode(['success' => false, 'message' => 'No data']);
            } else {
                echo json_encode($result);
            }
            
            // Finaliza a execução para não carregar o resto do site no AJAX
            $app->close();
        }

        // Renderiza o Template (HTML do formulário)
        ob_start();
        $uniqueId = rand(1000, 9999);
        $pluginParams = $this->params;
        include __DIR__ . '/tmpl/default.php';
        $formHtml = ob_get_clean();

        // Substitui a tag pelo HTML gerado
        $article->text = str_replace('{simplecontact}', $formHtml, $article->text);
    }
}