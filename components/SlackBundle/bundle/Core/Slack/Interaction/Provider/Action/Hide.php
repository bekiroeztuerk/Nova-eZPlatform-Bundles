<?php

/**
 * NovaeZSlackBundle Bundle.
 *
 * @package   Novactive\Bundle\eZSlackBundle
 *
 * @author    Novactive <s.morel@novactive.com>
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaeZSlackBundle/blob/master/LICENSE MIT Licence
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZSlackBundle\Core\Slack\Interaction\Provider\Action;

use eZ\Publish\Core\SignalSlot\Signal;
use Novactive\Bundle\eZSlackBundle\Core\Slack\Action;
use Novactive\Bundle\eZSlackBundle\Core\Slack\Attachment;
use Novactive\Bundle\eZSlackBundle\Core\Slack\Button;
use Novactive\Bundle\eZSlackBundle\Core\Slack\InteractiveMessage;

/**
 * Class Hide.
 */
class Hide extends ActionProvider
{
    /**
     * {@inheritdoc}
     */
    public function getAction(Signal $signal, int $index): ?Action
    {
        $content = $this->getContentForSignal($signal);
        if (null === $content || !$content->contentInfo->published || null === $content->contentInfo->mainLocationId) {
            return null;
        }

        $location = $this->repository->getLocationService()->loadLocation($content->contentInfo->mainLocationId);
        if ($location->hidden) {
            return null;
        }
        $button = new Button($this->getAlias(), '_t:action.hide', (string) $content->id);
        $button->setStyle(Button::DANGER_STYLE);

        return $button;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InteractiveMessage $message): Attachment
    {
        $action = $message->getAction();
        $value = (int) $action->getValue();

        $attachment = new Attachment();
        $attachment->setTitle('_t:action.hide');
        try {
            $content = $this->repository->getContentService()->loadContent($value);
            $locations = $this->repository->getLocationService()->loadLocations($content->contentInfo);
            foreach ($locations as $location) {
                $this->repository->getLocationService()->hideLocation($location);
            }
            $attachment->setColor('good');
            $attachment->setText('_t:action.locations.hid');
        } catch (\Exception $e) {
            $attachment->setColor('danger');
            $attachment->setText($e->getMessage());
        }

        return $attachment;
    }
}
