<?php

namespace DMKOroCRM\Bundle\ContactBundle\Provider;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\CalendarBundle\Provider\AbstractCalendarProvider;

use OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository;
use OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository;

class BirthdayCalendarProvider extends AbstractCalendarProvider
{
    const ALIAS                = 'birthdays';
    const BIRTHDAY_CALENDAR_ID = 122;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var BirthdayCalendarNormalizer */
    protected $birthdayCalendarNormalizer;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var bool */
    protected $myTasksEnabled = true;

    /** @var  bool */
    protected $calendarLabels = [
        self::BIRTHDAY_CALENDAR_ID => 'dmkorocrm.contact.menu.birthdays'
    ];

    /**
     * @param DoctrineHelper         $doctrineHelper
     * @param AclHelper              $aclHelper
     * @param BirthdayCalendarNormalizer $birthdayCalendarNormalizer
     * @param TranslatorInterface    $translator
     * @param bool                   $myTasksEnabled
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        AclHelper $aclHelper,
        BirthdayCalendarNormalizer $birthdayCalendarNormalizer,
        TranslatorInterface $translator
    ) {
        parent::__construct($doctrineHelper);
        $this->aclHelper              = $aclHelper;
        $this->birthdayCalendarNormalizer = $birthdayCalendarNormalizer;
        $this->translator             = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarDefaultValues($organizationId, $userId, $calendarId, array $calendarIds)
    {
        $result = [];

        if ($this->myTasksEnabled) {
            $result[self::BIRTHDAY_CALENDAR_ID] = [
                'calendarName'    => $this->translator->trans($this->calendarLabels[self::BIRTHDAY_CALENDAR_ID]),
                'removable'       => false,
                'position'        => -100,
                'backgroundColor' => '#F83A22',
                'options'         => [
//                     'widgetRoute'   => 'orocrm_task_widget_info',
//                     'widgetOptions' => [
//                         'title'         => $this->translator->trans('orocrm.task.info_widget_title'),
//                         'dialogOptions' => [
//                             'width' => 600
//                         ]
//                     ]
                ]
            ];
        } elseif (in_array(self::BIRTHDAY_CALENDAR_ID, $calendarIds)) {
            $result[self::BIRTHDAY_CALENDAR_ID] = null;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarEvents(
        $organizationId,
        $userId,
        $calendarId,
        $start,
        $end,
        $connections,
        $extraFields = []
    ) {
        if (!$this->myTasksEnabled) {
            return [];
        }

        if ($this->isCalendarVisible($connections, self::BIRTHDAY_CALENDAR_ID)) {
            $extraFields = $this->filterSupportedFields($extraFields, 'OroCRM\Bundle\ContactBundle\Entity\Contact');
            $qb          = $this->getContactListByTimeIntervalQueryBuilder($userId, $start, $end, $extraFields);
            $query       = $this->aclHelper->apply($qb);

            return $this->birthdayCalendarNormalizer->getBirthdays(self::BIRTHDAY_CALENDAR_ID, $query, $start, $end);
        }

        return [];
    }

    /**
     * Returns a query builder which can be used to get a list of tasks filtered by start and end dates
     *
     * @param int       $userId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string[]  $extraFields
     *
     * @return QueryBuilder
     */
    protected function getContactListByTimeIntervalQueryBuilder($userId, $startDate, $endDate, $extraFields = [])
    {
        /** @var ContactRepository $repo */
        $repo = $this->doctrineHelper->getEntityRepository('OroCRMContactBundle:Contact');

        if($startDate->format('Y') != $endDate->format('Y')) {

        	$qb = $repo->createQueryBuilder('c')
	        	->select('c.id, c.firstName, c.lastName, c.birthday, c.birthdaycal, c.createdAt, c.updatedAt')
	        	->where('c.birthdaycal >= :start1 AND c.birthdaycal <= :end1 OR
	        			c.birthdaycal >= :start2 AND c.birthdaycal <= :end2')
	        	->setParameter('start1', $startDate->format('md'))
	        	->setParameter('end1', '1231')
	        	->setParameter('start2', '0101')
	        	->setParameter('end2', $endDate->format('md'))
        	;
        }
        else {
        	$qb = $repo->createQueryBuilder('c')
	        	->select('c.id, c.firstName, c.lastName, c.birthdaycal, c.createdAt, c.updatedAt')
	        	->where('c.birthdaycal >= :start AND c.birthdaycal <= :end')
//	    	->where('MONTH(c.birthday) = :start ')
	        	->setParameter('start', $startDate->format('md'))
	        	->setParameter('end', $endDate->format('md'))
        	;
        }
    	if ($extraFields) {
    		foreach ($extraFields as $field) {
    			$qb->addSelect('c.' . $field);
    		}
    	}
//    	print_r('SQL ' . $qb->getQuery()->getSQL() . ' - ' .$startDate->format('Y-m-d') . ' - '. $endDate->format('Y-m-d'));

    	return $qb;
    }

    /**
     * @param array $connections
     * @param int   $calendarId
     * @param bool  $default
     *
     * @return bool
     */
    protected function isCalendarVisible($connections, $calendarId, $default = true)
    {
        return isset($connections[$calendarId])
            ? $connections[$calendarId]
            : $default;
    }
}
