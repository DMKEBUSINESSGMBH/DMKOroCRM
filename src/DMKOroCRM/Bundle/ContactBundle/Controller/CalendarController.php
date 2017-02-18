<?php

namespace DMKOroCRM\Bundle\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use DMKClub\Bundle\MemberBundle\Entity\Member;
use DMKClub\Bundle\MemberBundle\Entity\MemberFeeDiscount;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class CalendarController extends Controller
{
	/**
	 * @Route(
	 *      "/{contactId}/addCalendar",
	 *      name="dmkorocrm_contact_addcalender",
	 *      requirements={"contactId"="\d+"}
	 * )
	 * @Template("DMKOroCRMContactBundle:BirthdayCalendar:add.html.twig")
	 * @ParamConverter("contact", options={"id" = "contactId"})
	 */
	public function addAction(Contact $contact)
	{
		$calFlag = $contact->getBirthdaycal();
		if($calFlag) {
			$calFlag = 0;
		}
		else {
			$birthday = $contact->getBirthday();
			if($birthday) {
				$calFlag = $birthday->format('md');
			}
		}
		$contact->setBirthdaycal($calFlag);

		$em = $this->getDoctrine()->getManager();
		$em->persist($contact);
		$em->flush();

		return ['added' => $calFlag ? true :false];

	}

}
