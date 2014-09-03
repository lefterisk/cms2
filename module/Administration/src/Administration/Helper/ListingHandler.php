<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\SiteLanguageHelper;

class ListingHandler
{
    protected $modelHandler;
    protected $languageHelper;

    public function __construct(ModelHandler $modelHandler, SiteLanguageHelper $languageHelper)
    {
        $this->modelHandler   = $modelHandler;
        $this->languageHelper = $languageHelper;
    }

    public function getListing()
    {

        $results = $this->modelHandler->getModelTable()->fetchForListing($this->languageHelper->getPrimaryLanguageId());

        foreach ($results as $result) {
            var_dump($result);
        }
        return false;
    }


}