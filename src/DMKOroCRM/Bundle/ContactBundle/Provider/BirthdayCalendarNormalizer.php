<?php

namespace DMKOroCRM\Bundle\ContactBundle\Provider;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\ReminderBundle\Entity\Manager\ReminderManager;
use Symfony\Component\Translation\TranslatorInterface;

class BirthdayCalendarNormalizer
{
    /** @var ReminderManager */
    protected $reminderManager;
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param ReminderManager $reminderManager
     */
    public function __construct(TranslatorInterface $translator, ReminderManager $reminderManager)
    {
    	$this->translator      = $translator;
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

        $labelBirthday = $this->translator->trans('dmkorocrm.calendar.birthday');
        $labelBornAt = $this->translator->trans('dmkorocrm.calendar.bornOn');
        $tzUTC  = new \DateTimeZone('UTC');
        $items  = $query->getArrayResult();
        foreach ($items as $item) {
            $calDay = (int) $item['birthdaycal'];
            $year = $calDay > (int)$start->format('md') ? $yearStart : $yearEnd;
            /** @var \DateTime $birthday */
            $birthday = $item['birthday'];
            // Das Jahr ersetzen
            $day = $year . substr($birthday->format('c'), 4);
            $age = $birthday->diff(new \DateTime($day, $tzUTC))->y;

            $result[] = [
                'calendar'    => $calendarId,
                'id'          => $item['id'],
                'title'       => $item['firstName'] . ' ' .$item['lastName']. "\n(". $age. '. '.$labelBirthday.')',
                'description' => $labelBornAt.' <b>'.$birthday->format('d.m.Y').'</b>',
                'start'       => $day,
                'end'         => $day,
                'allDay'      => true,
                'createdAt'   => $item['createdAt']->format('c'),
                'updatedAt'   => $item['updatedAt']->format('c'),
                'editable'    => false,
                'removable'   => false
            ];
        }

        return $result;
    }
}
