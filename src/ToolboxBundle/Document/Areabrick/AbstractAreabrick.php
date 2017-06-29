<?php

namespace ToolboxBundle\Document\Areabrick;

use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Tag\Area\Info;

use ToolboxBundle\Service\ConfigManager;

abstract class AbstractAreabrick extends AbstractTemplateAreabrick
{
    /**
     * @var ConfigManager
     */
    var $configManager;

    /**
     * @var string
     */
    var $areaBrickType = 'internal';

    const AREABRICK_TYPE_INTERNAL = 'internal';

    const AREABRICK_TYPE_EXTERNAL = 'external';

    /**
     * @param string $type
     */
    public function setAreaBrickType($type = self::AREABRICK_TYPE_INTERNAL) {

        $this->areaBrickType = $type;
    }

    /**
     * @return string
     */
    public function getAreaBrickType() {

        return $this->areaBrickType;
    }

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return \ToolboxBundle\Service\ConfigManager object
     */
    public function getConfigManager()
    {
        $space = $this->areaBrickType === self::AREABRICK_TYPE_INTERNAL
            ? ConfigManager::AREABRICK_NAMESPACE_INTERNAL
            : ConfigManager::AREABRICK_NAMESPACE_EXTERNAL;

        return $this->configManager->setAreaNameSpace($space);
    }

    /**
     * @return \ToolboxBundle\Service\BrickConfigBuilder
     */
    public function getBrickConfigBuilder()
    {
        return $this->container->get('toolbox.brick_config_builder');
    }

    /**
     * @param Info $info
     *
     * @throws \Exception
     */
    public function action(Info $info)
    {
        if(!$this->getConfigManager() instanceof ConfigManager) {
            throw new \Exception('Please register your AreaBrick "' . $info->getId() . '" as a service and set "toolbox.area.brick.base_brick" as parent.');
        } else if($this->areaBrickType == self::AREABRICK_TYPE_INTERNAL && !in_array($info->getId(), $this->configManager->getValidCoreBricks())) {
            throw new \Exception('The "' . $info->getId() . '" AreaBrick has a invalid AreaBrickType. Please set type to "' . self::AREABRICK_TYPE_EXTERNAL . '".');
        } else if($this->areaBrickType == self::AREABRICK_TYPE_EXTERNAL && in_array($info->getId(), $this->configManager->getValidCoreBricks())) {
            throw new \Exception('The "' . $info->getId() . '" AreaBrick is using a reserved id. Please change the id of your custom AreaBrick.');
        }

        $configNode = $this->getConfigManager()->getAreaConfig($this->getId());
        $configWindowData = $this->getBrickConfigBuilder()->buildElementConfig($this->getId(), $this->getName(), $info, $configNode);

        $view = $info->getView();
        $view->elementConfigBar = $configWindowData;
    }

    /**
     * @inheritDoc
     */
    public function getTemplateLocation()
    {
        return static::TEMPLATE_LOCATION_BUNDLE;
    }

    /**
     * @inheritDoc
     */
    public function getTemplateSuffix()
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagOpen(Info $info)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagClose(Info $info)
    {
        return '';
    }

}