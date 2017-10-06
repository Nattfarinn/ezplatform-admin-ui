<?php
declare(strict_types=1);

namespace EzPlatformAdminUiBundle\Templating\Twig;


use EzPlatformAdminUi\Service\TabService;
use EzPlatformAdminUi\Tab\Event\TabEvent;
use EzPlatformAdminUi\Tab\Event\TabEvents;
use EzPlatformAdminUi\Tab\Event\TabGroupEvent;
use EzPlatformAdminUi\Tab\TabGroup;
use EzPlatformAdminUi\Tab\TabInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig_Extension;
use Twig_SimpleFunction;

class TabExtension extends Twig_Extension
{
    /** @var Environment */
    protected $twig;

    /** @var TabService */
    protected $tabService;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param Environment $twig
     * @param TabService $tabService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        Environment $twig,
        TabService $tabService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->twig = $twig;
        $this->tabService = $tabService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'ez_platform_tabs',
                [$this, 'renderTabGroup'],
                ['is_safe' => ['html']]
            )
        ];
    }

    /**
     * @param string $groupIdentifier
     * @param array $parameters
     *
     * @return string
     */
    public function renderTabGroup(string $groupIdentifier, array $parameters): string
    {
        $tabGroup = $this->tabService->getTabGroup($groupIdentifier);
        $tabGroupEvent = $this->dispatchTabGroupPreRenderEvent($tabGroup);

        $tabs = [];
        foreach ($tabGroupEvent->getData()->getTabs() as $tab) {
            $tabEvent = $this->dispatchTabPreRenderEvent($tab);
            $tabs[] = $this->composeTabParameters($tabEvent->getData(), $parameters);
        }

        return $this->twig->render(
            'EzPlatformAdminUiBundle:parts/tab:tabs.html.twig',
            array_merge(['tabs' => $tabs, 'group' => $groupIdentifier], $parameters)
        );
    }

    /**
     * @param TabGroup $tabGroup
     *
     * @return TabGroupEvent
     */
    private function dispatchTabGroupPreRenderEvent(TabGroup $tabGroup): TabGroupEvent
    {
        $tabGroupEvent = new TabGroupEvent();
        $tabGroupEvent->setData($tabGroup);

        $this->eventDispatcher->dispatch(TabEvents::TAB_GROUP_PRE_RENDER, $tabGroupEvent);

        return $tabGroupEvent;
    }

    /**
     * @param TabInterface $tab
     *
     * @return TabEvent
     */
    private function dispatchTabPreRenderEvent(TabInterface $tab): TabEvent
    {
        $tabEvent = new TabEvent();
        $tabEvent->setData($tab);

        $this->eventDispatcher->dispatch(TabEvents::TAB_PRE_RENDER, $tabEvent);

        return $tabEvent;
    }

    /**
     * @param TabInterface $tab
     * @param array $parameters
     *
     * @return array
     */
    private function composeTabParameters(TabInterface $tab, array $parameters): array
    {
        return [
            'name' => $tab->getName(),
            'view' => $tab->renderView($parameters),
            'identifier' => $tab->getIdentifier(),
        ];
    }
}
