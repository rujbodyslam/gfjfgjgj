<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Url\HostChecker;
use Magento\Framework\Url\ParamEncoder;

/**
 * Test class for \Magento\Backend\Model\Url
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_model;

    protected $_areaFrontName = 'backendArea';

    /**
     * Mock menu model
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formKey;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendHelperMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paramsResolverMock;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var HostChecker
     */
    private $hostChecker;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var ParamEncoder
     */
    private $paramEncoder;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_menuMock = $this->getMock(
            \Magento\Backend\Model\Menu::class,
            [],
            [$this->getMock(\Psr\Log\LoggerInterface::class)]
        );

        $this->_menuConfigMock = $this->getMock(\Magento\Backend\Model\Menu\Config::class, [], [], '', false);
        $this->_menuConfigMock->expects($this->any())->method('getMenu')->will($this->returnValue($this->_menuMock));

        $this->_formKey = $this->getMock(
            \Magento\Framework\Data\Form\FormKey::class,
            ['getFormKey'],
            [],
            '', false
        );
        $this->_formKey->expects($this->any())->method('getFormKey')->will($this->returnValue('salt'));

        $mockItem = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $mockItem->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $mockItem->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $mockItem->expects($this->any())->method('getId')->will(
            $this->returnValue('Magento_Backend::system_acl_roles')
        );
        $mockItem->expects($this->any())->method('getAction')->will($this->returnValue('adminhtml/user_role'));

        $this->_menuMock->expects($this->any())->method('get')->with(
            $this->equalTo('Magento_Backend::system_acl_roles')
        )->will($this->returnValue($mockItem));

        $helperMock = $this->getMock(\Magento\Backend\Helper\Data::class, [], [], '', false);
        $helperMock->expects($this->any())->method('getAreaFrontName')->will(
            $this->returnValue($this->_areaFrontName)
        );
        $this->_scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_scopeConfigMock->expects($this->any())->method('getValue')->with(
            \Magento\Backend\Model\Url::XML_PATH_STARTUP_MENU_ITEM
        )->will($this->returnValue('Magento_Backend::system_acl_roles'));

        $this->_authSessionMock = $this->getMock(
            \Magento\Backend\Model\Auth\Session::class,
            [],
            [],
            '',
            false,
            false
        );
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_encryptor = $this->getMock(\Magento\Framework\Encryption\Encryptor::class, null, [], '', false);
        $this->_paramsResolverMock = $this->getMock(
            \Magento\Framework\Url\RouteParamsResolverFactory::class,
            [],
            [],
            '',
            false
        );
        $this->_paramsResolverMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue(
                $this->getMock(\Magento\Framework\Url\RouteParamsResolver::class, [], [], '', false)
            )
        );

        $this->hostChecker = $this->getMockBuilder(HostChecker::class)
            ->setMethods(['isOwnOrigin'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paramEncoder = $this->objectManager->getObject(ParamEncoder::class);

        /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $objectManagerMock */
        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerMock->expects(self::at(0))->method('get')->with(HostChecker::class)
            ->willReturn($this->hostChecker);

        $objectManagerMock->expects(self::at(1))->method('get')->with(ParamEncoder::class)
            ->willReturn($this->paramEncoder);

        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $this->_model = $this->objectManager->getObject(
            \Magento\Backend\Model\Url::class,
            [
                'scopeConfig'                => $this->_scopeConfigMock,
                'backendHelper'              => $helperMock,
                'formKey'                    => $this->_formKey,
                'menuConfig'                 => $this->_menuConfigMock,
                'authSession'                => $this->_authSessionMock,
                'encryptor'                  => $this->_encryptor,
                'routeParamsResolverFactory' => $this->_paramsResolverMock,
            ]
        );
        $this->_paramsResolverMock->expects($this->any())->method('create')
            ->will($this->returnValue(
                $this->getMock(\Magento\Framework\Url\RouteParamsResolver::class, [], [], '', false)
            )
        );

        $this->_requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->_model->setRequest($this->_requestMock);
    }

    public function testFindFirstAvailableMenuDenied()
    {
        $user = $this->getMock(\Magento\User\Model\User::class, [], [], '', false);
        $user->expects($this->once())->method('setHasAvailableResources')->with($this->equalTo(false));
        /** @var \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject $mockSession */
        $mockSession = $this->getMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['getUser', 'isAllowed'],
            [],
            '',
            false
        );

        $mockSession->expects($this->any())->method('getUser')->will($this->returnValue($user));

        $this->_model->setSession($mockSession);

        $this->_menuMock->expects($this->any())->method('getFirstAvailableChild')->will($this->returnValue(null));

        $this->assertEquals('*/*/denied', $this->_model->findFirstAvailableMenu());
    }

    public function testFindFirstAvailableMenu()
    {
        $user = $this->getMock(\Magento\User\Model\User::class, [], [], '', false);
        /** @var  \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject $mockSession */
        $mockSession = $this->getMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['getUser', 'isAllowed'],
            [],
            '',
            false
        );

        $mockSession->expects($this->any())->method('getUser')->will($this->returnValue($user));

        $this->_model->setSession($mockSession);

        $itemMock = $this->getMock(\Magento\Backend\Model\Menu\Item::class, [], [], '', false);
        $itemMock->expects($this->once())->method('getAction')->will($this->returnValue('adminhtml/user'));
        $this->_menuMock->expects($this->any())->method('getFirstAvailable')->will($this->returnValue($itemMock));

        $this->assertEquals('adminhtml/user', $this->_model->findFirstAvailableMenu());
    }

    public function testGetStartupPageUrl()
    {
        $this->assertEquals('adminhtml/user_role', (string)$this->_model->getStartupPageUrl());
    }

    public function testGetAreaFrontName()
    {
        $helperMock = $this->getMock(\Magento\Backend\Helper\Data::class, [], [], '', false);
        $helperMock->expects($this->once())->method('getAreaFrontName')->will(
            $this->returnValue($this->_areaFrontName)
        );

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $urlModel = $helper->getObject(
            \Magento\Backend\Model\Url::class,
            [
                'backendHelper'              => $helperMock,
                'authSession'                => $this->_authSessionMock,
                'routeParamsResolverFactory' => $this->_paramsResolverMock
            ]
        );
        $urlModel->getAreaFrontName();
    }

    /**
     * Check that secret key generation is based on usage of routeName passed as method param
     * Params are not equals
     */
    public function testGetSecretKeyGenerationWithRouteNameAsParamNotEquals()
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyWithRouteName = $this->_model->getSecretKey($routeName, $controllerName, $actionName);
        $keyWithoutRouteName = $this->_model->getSecretKey(null, $controllerName, $actionName);
        $keyDummyRouteName = $this->_model->getSecretKey('dummy', $controllerName, $actionName);

        $this->assertNotEquals($keyWithRouteName, $keyWithoutRouteName);
        $this->assertNotEquals($keyWithRouteName, $keyDummyRouteName);
    }

    /**
     * Check that secret key generation is based on usage of routeName passed as method param
     * Params are equals
     */
    public function testGetSecretKeyGenerationWithRouteNameAsParamEquals()
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyWithRouteName1 = $this->_model->getSecretKey($routeName, $controllerName, $actionName);
        $keyWithRouteName2 = $this->_model->getSecretKey($routeName, $controllerName, $actionName);

        $this->assertEquals($keyWithRouteName1, $keyWithRouteName2);
    }

    /**
     * Check that secret key generation is based on usage of routeName extracted from request
     */
    public function testGetSecretKeyGenerationWithRouteNameInRequest()
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyFromParams = $this->_model->getSecretKey($routeName, $controllerName, $actionName);

        $this->_requestMock->expects($this->exactly(3))->method('getBeforeForwardInfo')->will(
            $this->returnValue(null)
        );
        $this->_requestMock->expects($this->once())->method('getRouteName')->will($this->returnValue($routeName));
        $this->_requestMock->expects($this->once())->method('getControllerName')->will(
            $this->returnValue($controllerName)
        );
        $this->_requestMock->expects($this->once())->method('getActionName')->will($this->returnValue($actionName));
        $this->_model->setRequest($this->_requestMock);

        $keyFromRequest = $this->_model->getSecretKey();
        $this->assertEquals($keyFromParams, $keyFromRequest);
    }

    /**
     * Check that secret key generation is based on usage of routeName extracted from request Forward info
     */
    public function testGetSecretKeyGenerationWithRouteNameInForwardInfo()
    {
        $routeName = 'adminhtml';
        $controllerName = 'catalog';
        $actionName = 'index';

        $keyFromParams = $this->_model->getSecretKey($routeName, $controllerName, $actionName);

        $this->_requestMock->expects($this->at(0))->method('getBeforeForwardInfo')->with('route_name')->will(
            $this->returnValue('adminhtml')
        );

        $this->_requestMock->expects($this->at(1))->method('getBeforeForwardInfo')->with('route_name')->will(
            $this->returnValue('adminhtml')
        );

        $this->_requestMock->expects($this->at(2))->method('getBeforeForwardInfo')->with('controller_name')->will(
            $this->returnValue('catalog')
        );

        $this->_requestMock->expects($this->at(3))->method('getBeforeForwardInfo')->with('controller_name')->will(
            $this->returnValue('catalog')
        );

        $this->_requestMock->expects($this->at(4))->method('getBeforeForwardInfo')->with('action_name')->will(
            $this->returnValue('index')
        );

        $this->_requestMock->expects($this->at(5))->method('getBeforeForwardInfo')->with('action_name')->will(
            $this->returnValue('index')
        );

        $this->_model->setRequest($this->_requestMock);
        $keyFromRequest = $this->_model->getSecretKey();
        $this->assertEquals($keyFromParams, $keyFromRequest);
    }

    public function testGetUrlWithUrlInRoutePath()
    {
        $routePath = 'https://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor';
        static::assertEquals($routePath, $this->_model->getUrl($routePath));
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        // reset ObjectManager instance.
        $reflection = new \ReflectionClass(\Magento\Framework\App\ObjectManager::class);
        $reflectionProperty = $reflection->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, null);
    }
}
