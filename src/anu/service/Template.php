<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 23.12.2017
 * Time: 19:06
 */

namespace anu\service;

use anu\base\Component;
use anu\base\Event;
use anu\events\RegisterCpNavEvent;
use anu\helper\Url;
use anu\web\twig\IncludeResourceTokenParser;
use Twig\Environment;

class Template extends Component{

    const REGISTER_CP_NAV = 'registerCpNav';

    /**
     * @var \Twig_Loader_Filesystem $_loader
     */
    private $_loader = null;
    /** @var \Twig_Environment[] $_twig */
    private $_twig = null;

    /** @var string[] $jsFiles js files  */
    private $_jsFiles = [];

    /** @var string[] $cssFiles css files  */
    private $_cssFiles = [];

    private $_jsCode = [];

    /** @var string $assetUrl */
    private $assetUrl;


    const TEMPLATE_MODE_CP = 'cp';
    const TEMPLATE_MODE_SITE = 'site';

    private $_cpTwig;
    private $_siteTwig;

    /**
     * @var
     */
    private $_objectDefined;


    private $_templateMode = self::TEMPLATE_MODE_CP;


    /**
     * Returns the Twig environment.
     *
     * @return Environment
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function getTwig(): Environment
    {
        return $this->_templateMode === self::TEMPLATE_MODE_CP
            ? $this->_cpTwig ?? ($this->_cpTwig = $this->createTwig($this->_templateMode))
            : $this->_siteTwig ?? ($this->_siteTwig = $this->createTwig($this->_templateMode));
    }


    /**
     * Creates a new Twig environment.
     *
     * @return Environment
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     * @throws \anu\base\InvalidRouteException
     */
    public function createTwig($templateMode): Environment
    {
        if(isset($this->_twig[$templateMode])){
            return $this->_twig[$templateMode];
        }

        $this->_loader[$templateMode] = new \Twig_Loader_Filesystem(\Anu::$app->config->getTemplatePath($templateMode));

        $this->_twig[$templateMode] = new \Twig_Environment($this->_loader[$templateMode], [
            'dev'   => true,
            'debug' => true,
            'strict_variables' => true,
        ]);

        $this->_twig[$templateMode]->addGlobal('anu',  \Anu::$app);
        $paths = \Anu::$app->getConfig()->getGeneral()['assets'];

        $this->_twig[$templateMode]->addExtension(new \Twig_Extension_Debug());
        $this->_twig[$templateMode]->addGlobal('assetUrl',  $paths['assetUrl']);
        $this->_twig[$templateMode]->addGlobal('baseUrl', BASE_URL);
        $this->_twig[$templateMode]->addGlobal('currentUser', \Anu::$app->getUser()->currentUser());
        $arrayFilter = new \Twig_SimpleFilter('filter', 'array_filter');
        $this->_twig[$templateMode]->addFilter($arrayFilter);

        $this->_twig[$templateMode]->addTokenParser(new IncludeResourceTokenParser('includeJsFile'));
        $this->_twig[$templateMode]->addTokenParser(new IncludeResourceTokenParser('includeCssFile'));


        if(\Anu::$app->getRequest()->isCpRequest()){
            $renderCpNav = new \Twig_Function('renderCpNav', function () {
                $event = new RegisterCpNavEvent([
                    'items' => [
                        'settings'  => [
                            'label' => 'settings',
                            'sub'   => [
                                ['label' => 'Fields', 'url' => Url::to('admin/fields')],
                                ['label' => 'Sections', 'url' => Url::to('admin/sections')],
                            ]
                        ]
                    ]
                ]);

                $this->trigger(self::REGISTER_CP_NAV, $event );
                echo $this->render('layout/partials/menuItem.twig', [
                    'items' => $event->items
                ]);
            });
            $this->_twig[$templateMode]->addFunction($renderCpNav);
        }

        $urlFilter = new \Twig_Filter('url', function ($string) {
            return Url::to($string);
        });
        $this->_twig[$templateMode]->addFilter($urlFilter);

        $translateFilter = new \Twig_Filter('t', function ($string, $category = 'anu') {
            return \Anu::t($category, $string);
        });
        $this->_twig[$templateMode]->addFilter($translateFilter);


        $renderFooter = new \Twig_Function('renderFooter', function () {
            echo $this->render('_partials/footer.twig', [
                'jsCode'    => $this->getJsCode()
            ]);
        });
        $this->_twig[$templateMode]->addFunction($renderFooter);
        return $this->_twig[$templateMode];
    }

    /**
     * @param $template
     * @param $variables
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function render($template, $variables){
        return $this->getTwig()->render($template, $variables);
    }

    public function toggleTemplateMode(){
        if($this->_templateMode === self::TEMPLATE_MODE_CP){
            $this->_templateMode = self::TEMPLATE_MODE_SITE;
        }else{
            $this->_templateMode = self::TEMPLATE_MODE_CP;
        }
    }

    public function setTemplateMode($templateMode){
        $this->_templateMode = $templateMode;
    }

    /**
     *
     */
    public function init(){
        self::on(self::REGISTER_CP_NAV, function(RegisterCpNavEvent $event){

        });
    }


    /**
     * @param $fileName
     */
    public function includeJsFile($fileName){
        if(!in_array($fileName, $this->_jsFiles)){
            $this->_jsFiles[] = $fileName;
        }
    }

    /**
     * @param $fileName
     */
    public function includeCssFile($fileName){
        if(!in_array($fileName, $this->_cssFiles)){
            $this->_cssFiles[] = $fileName;
        }
    }

    /**
     * @return array
     */
    public function getCssFiles(){
        return $this->_cssFiles;
    }

    /**
     * @return array
     */
    public function getJsFiles(){
        return $this->_jsFiles;
    }

    /**
     * @param $code
     */
    public function addJsCode($code){
        if(!in_array($code, $this->_jsCode)){
            $this->_jsCode[] = $code;
        }
    }


    /**
     * Add an element to the anu js object
     *
     * @param $object
     * @param $index
     */
    public function addAnuJsObject($object, $index){
        if($this->_objectDefined === null){
            $this->addJsCode('
                var anu = {};
            ');
            $this->_objectDefined = true;
        }

        $this->addJsCode('
            anu["' . $index.  '"] = ' . json_encode($object) . ';
        ');
    }

    /**
     * @return string
     */
    public function getJsCode(){
        $js = $this->_combineJs($this->_jsCode);
        return "<script type=\"text/javascript\">\n/*<![CDATA[*/\n" .$js."\n/*]]>*/\n</script>";
    }

    // private
    //================================================================

    /**
     * @param $js
     * @return string
     */
    private function _combineJs($js)
    {
        return implode("\n\n", $js);
    }
}

