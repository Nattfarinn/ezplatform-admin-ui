<?php
declare(strict_types=1);

namespace EzPlatformAdminUi\Tab\LocationView;


use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzPlatformAdminUi\Form\Data\Content\Translation\TranslationRemoveData;
use EzPlatformAdminUi\Form\Factory\FormFactory;
use EzPlatformAdminUi\Tab\AbstractTab;
use EzPlatformAdminUi\Tab\OrderedTabInterface;
use EzPlatformAdminUi\UI\Dataset\DatasetFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;

class TranslationsTab extends AbstractTab implements OrderedTabInterface
{
    /** @var DatasetFactory */
    protected $datasetFactory;

    /** @var FormFactory */
    protected $formFactory;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /**
     * @param Environment $twig
     * @param TranslatorInterface $translator
     * @param DatasetFactory $datasetFactory
     * @param FormFactory $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        DatasetFactory $datasetFactory,
        FormFactory $formFactory,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($twig, $translator);

        $this->datasetFactory = $datasetFactory;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
    }

    public function getIdentifier(): string
    {
        return 'translations';
    }

    public function getName(): string
    {
        /** @Desc("Translations") */
        return $this->translator->trans('tab.name.translations', [], 'locationview');
    }

    public function getOrder(): int
    {
        return 600;
    }

    public function renderView(array $parameters): string
    {
        /** @var Location $location */
        $location = $parameters['location'];
        /** @var Content $content */
        $content = $parameters['content'];
        $versionInfo = $content->getVersionInfo();
        $translationsDataset = $this->datasetFactory->translations();
        $translationsDataset->load($versionInfo);

        $formTranslationRemoveForm = $this->createTranslationRemoveForm(
            $location,
            $translationsDataset->getLanguageCodes()
        );

        $viewParameters = [
            'translations' => $translationsDataset->getTranslations(),
            'form_translation_remove' => $formTranslationRemoveForm->createView(),
        ];

        return $this->twig->render(
            'EzPlatformAdminUiBundle:content/tab:translations.html.twig',
            array_merge($viewParameters, $parameters)
        );
    }

    /**
     * @param Location $location
     * @param array $languageCodes
     *
     * @return FormInterface
     */
    private function createTranslationRemoveForm(Location $location, array $languageCodes): FormInterface
    {
        $translationRemoveData = new TranslationRemoveData(
            $location->getContentInfo(),
            array_combine($languageCodes, array_fill_keys($languageCodes, false))
        );

        $locationViewUrl = $this->urlGenerator->generate(
            $location,
            ['_fragment' => 'ez-tab-location-view-translations']
        );

        return $this->formFactory->removeTranslation(null, $translationRemoveData, $locationViewUrl, $locationViewUrl);
    }
}
