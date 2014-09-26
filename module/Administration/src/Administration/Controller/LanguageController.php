<?php
namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LanguageController extends AbstractActionController
{
    public function indexAction()
    {
        $requestedLanguage   = $this->params()->fromRoute('language');
        $sessionContainer    = $this->getServiceLocator()->get('Session');
        $adminLanguageHelper = $this->getServiceLocator()->get('AdminLanguages');

        if (is_array($adminLanguageHelper->getLanguages())) {
            foreach ($adminLanguageHelper->getLanguages() as $id => $language) {
                if ($id == $requestedLanguage) {
                    $sessionContainer->locale = $language['code'];
                }
            }
        }

        //redirect to page that called for language change
        return $this->redirect()->toUrl($this->getRequest()->getHeader('Referer')->uri()->getPath());
    }
}