<?php

/**
 * (c) Spryker Systems GmbH copyright protected.
 */

namespace Spryker\Zed\Cms\Communication\Form;

use Spryker\Zed\Cms\Dependency\Facade\CmsToUrlInterface;
use Spryker\Zed\Gui\Communication\Form\AbstractForm;
use Orm\Zed\Url\Persistence\SpyUrlQuery;
use Symfony\Component\Validator\Context\ExecutionContext;

class CmsRedirectForm extends AbstractForm
{

    const ADD = 'add';
    const UPDATE = 'update';
    const ID_REDIRECT = 'id_redirect';
    const FROM_URL = 'from_url';
    const TO_URL = 'to_url';
    const STATUS = 'status';

    /**
     * @var SpyUrlQuery
     */
    protected $urlByIdQuery;

    /**
     * @var CmsToUrlInterface
     */
    protected $urlFacade;

    /**
     * @var string
     */
    protected $formType;

    /**
     * @var string
     */
    protected $redirectUrl;

    /**
     * @param string $type
     */

    /**
     * @param SpyUrlQuery $urlByIdQuery
     * @param CmsToUrlInterface $urlFacade
     * @param string $formType
     */
    public function __construct(SpyUrlQuery $urlByIdQuery, CmsToUrlInterface $urlFacade, $formType)
    {
        $this->urlByIdQuery = $urlByIdQuery;
        $this->urlFacade = $urlFacade;
        $this->formType = $formType;
    }

    /**
     * @return CmsRedirectForm
     */
    protected function buildFormFields()
    {
        $urlConstraints = $this->getConstraints()->getMandatoryConstraints();

        $urlConstraints[] = $this->getConstraints()->createConstraintCallback([
            'methods' => [
                function ($url, ExecutionContext $context) {
                    if ($this->urlFacade->hasUrl($url) && $this->redirectUrl !== $url) {
                        $context->addViolation('Url is already used');
                    }
                },
            ],
        ]);

        return $this->addHidden(self::ID_REDIRECT)
            ->addText(self::FROM_URL, [
                'label' => 'URL',
                'constraints' => $urlConstraints,
            ])
            ->addText(self::TO_URL, [
                'label' => 'To URL',
                'constraints' => $this->getConstraints()->getMandatoryConstraints(),
            ])
            ->addText(self::STATUS);
    }

    /**
     * @return array
     */
    protected function populateFormFields()
    {
        $url = $this->urlByIdQuery->findOne();

        if (isset($url)) {
            $this->redirectUrl = $url->getUrl();

            return [
                self::FROM_URL => $url->getUrl(),
                self::TO_URL => $url->getToUrl(),
                self::STATUS => $url->getStatus(),
            ];
        }
    }

}
