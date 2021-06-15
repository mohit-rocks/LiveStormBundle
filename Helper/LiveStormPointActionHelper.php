<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Helper;

/**
 * Class LiveStormPointActionHelper.
 */
class LiveStormPointActionHelper
{
    /**
     * Validate the data and configured actions.
     * Mautic will add points to contact if this functions returns true.
     *
     * @param array $eventDetails
     *                            Event details with all interaction attributes and values
     * @param array $action
     *                            Action attributes which are set while creating point action
     */
    public static function validateUserInteraction(array $action, array $eventDetails): bool
    {
        $configured_event_id          = $action['properties']['event'];
        $configured_interaction_type  = $action['properties']['event_interaction'];
        $configured_interaction_count = $action['properties']['event_interaction_count'];
        $configured_operator          = $action['properties']['operator'];

        if ($eventDetails['event_id'] !== $configured_event_id) {
            return false;
        }
        switch ($configured_operator) {
            case 'lt':
                return $eventDetails['interactions'][$configured_interaction_type] < $configured_interaction_count;
            case 'lte':
                return $eventDetails['interactions'][$configured_interaction_type] <= $configured_interaction_count;
            case 'gt':
                return $eventDetails['interactions'][$configured_interaction_type] > $configured_interaction_count;
            case 'gte':
                return $eventDetails['interactions'][$configured_interaction_type] >= $configured_interaction_count;
            case 'eq':
                return $eventDetails['interactions'][$configured_interaction_type] == $configured_interaction_count;
            default:
                return false;
        }
    }
}
