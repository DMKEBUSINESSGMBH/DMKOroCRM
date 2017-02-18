<?php

namespace DMKOroCRM\Bundle\ContactBundle\Provider;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\ReminderBundle\Entity\Manager\ReminderManager;

class BirthdayCalendarNormalizer
{
    /** @var ReminderManager */
    protected $reminderManager;

    /**
     * @param ReminderManager $reminderManager
     */
    public function __construct(ReminderManager $reminderManager)
    {
        $this->reminderManager = $reminderManager;
    }

    /**
     * @param int           $calendarId
     * @param AbstractQuery $query
     *
     * @return array
     */
    public function getBirthdays($calendarId, AbstractQuery $query, $start, $end)
    {
        $result = [];

        $yearStart = $start->format('Y');
        $yearEnd = $end->format('Y');

        $items  = $query->getArrayResult();
        foreach ($items as $item) {
            $calDay = (int) $item['birthdaycal'];
            $year = $calDay > (int)$start->format('md') ? $yearStart : $yearEnd;
        	/** @var \DateTime $start */
            $day = $item['birthday'];
            // Das Jahr ersetzen
            $day = $year . substr($day->format('c'), 4);

            $result[] = [
                'calendar'    => $calendarId,
                'id'          => $item['id'],
                'title'       => $item['firstName'] . ' ' .$item['lastName'],
                'description' => 'Birthday',
                'start'       => $day,
                'end'         => $day,
                'allDay'      => true,
                'createdAt'   => $item['createdAt']->format('c'),
                'updatedAt'   => $item['updatedAt']->format('c'),
                'editable'    => false,
                'removable'   => false
            ];
        }
//print_r(['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d'), 'c' => count($result)]);
//        $this->reminderManager->applyReminders($result, 'OroCRM\Bundle\TaskBundle\Entity\Task');

        return $result;
    }
}
