<?php
namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Contact;
use AppBundle\Entity\Group;

class MainController extends Controller
{
    public function contactListAction(Request $request)
    {
        $contacts = $this->getDoctrine()
        ->getRepository('AppBundle:Contact')
        ->findBy(array(), array('name' => 'ASC'));
    	
        $defaultData = array();
        $form_search = $this->createFormBuilder($defaultData)
        ->add('search', 'text', array('attr' => array('placeholder' => 'Search for...')))
        ->getForm();

        $form_search->handleRequest($request);

        if ($form_search->isValid()) {
            $data = $form_search->getData();
            $search = $data['search'];
            if (strlen($search) < 50) {
                $contacts = self::search($search);

                if ($contacts) {
                    return $this->render(
                        'AppBundle:contact_list.html.twig',
                        array('contacts' => $contacts, 'form_search' => $form_search->createView())
    					);
    			} else {
                    return $this->render(
                        'AppBundle:contact_list.html.twig',
                        array(
                            'contacts' => 'unsuccessful_search',
                            'form_search' => $form_search->createView(),
                            'search' => $search
                        )
                    );
                }
            }
        }
    	
        if ($contacts == null || $contacts == '') {
            $contacts = 'empty';
        }
        return $this->render(
            'AppBundle:contact_list.html.twig', 
            array('contacts' => $contacts, 'form_search' => $form_search->createView())
        	);
    }
    
    private function search($text_to_search)
    {
        $em = $this->getDoctrine()->getManager();
		
        $groups = $em->getRepository('AppBundle:Group')->createQueryBuilder('o')
        ->where('o.name LIKE :text_to_search')
        ->setParameter('text_to_search','%'.$text_to_search.'%')
        ->getQuery()
        ->getResult();
		
        $contacts_result = array();
        foreach ($groups as $group_key => $group_value) {
            $contacts = $group_value->getContacts();
            foreach ($contacts as $contact_key => $contact_value) {
                if(self::isUniqueContact($contacts_result, $contact_value)) {
                    $contacts_result[] = $contact_value;
                }
            }
        }
		
        $contacts = $em->getRepository('AppBundle:Contact')->createQueryBuilder('o')
        ->where('o.name LIKE :text_to_search')
        ->orWhere('o.surname LIKE :text_to_search')
        ->orWhere('o.email_address LIKE :text_to_search')
        ->orWhere('o.phone_number LIKE :text_to_search')
        ->setParameter('text_to_search','%'.$text_to_search.'%')
        ->getQuery()
        ->getResult();
		
        foreach ($contacts as $contact_key => $contact_value) {
            if(self::isUniqueContact($contacts_result, $contact_value)) {
                $contacts_result[] = $contact_value;
            }
        }
		
        return $contacts_result;
    }
   	
    private function isUniqueContact($contacts, $new_contact)
    {
        foreach ($contacts as $contact_key => $contact_value) {
            if ($contact_value->getId() == $new_contact->getId()) {
                return false;
            }
        }
        return true;
    }
    
    public function createContactAction(Request $request)
    {
        $contact = new Contact();
    	 
        $form = $this->createFormBuilder(
            $contact,
            array('validation_groups' => array('Create'))
        )
        ->add('name', 'text')
        ->add('surname', 'text')
        ->add('email_address', 'text')
        ->add('phone_number', 'text')
        ->add('groups', 'entity', array(
            'class' => 'AppBundle:Group',
            'property' => 'name',
            'multiple' => true,
            'expanded' => true,
            )
        )
        ->add('submit_button', 'submit')
        ->getForm();
    	
        $form->handleRequest($request);
    	
        if ($form->isValid()) {
            self::addContact($contact);
            return $this->redirect($this->generateUrl('contact_list'));
        }
    			 
        return $this->render(
            'AppBundle:create_contact.html.twig', 
             array('form' => $form->createView())
        );
    }
    
    public function editContactAction(Request $request, $contact_id)
    {
        $em = $this->getDoctrine()->getManager();
    	 
        $contact = $em->getRepository('AppBundle:Contact')->find($contact_id);
    	
        $form = $this->createFormBuilder(
            $contact,
            array('validation_groups' => array('Edit')))
        ->add('name', 'text')
        ->add('surname', 'text')
        ->add('email_address', 'text')
        ->add('phone_number', 'text')
        ->add('groups', 'entity', array(
            'class' => 'AppBundle:Group',
            'property' => 'name',
            'multiple' => true,
            'expanded' => true,
            )
        )
        ->add('submit_button', 'submit')
        ->getForm();
    			 
        $form->handleRequest($request);
    			 
        if ($form->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('contact_list'));
        }
    	
        return $this->render(
            'AppBundle:edit_contact.html.twig',
            array('form' => $form->createView())
        );
    }
    
    public function removeContactAction(Request $request, $contact_id)
    {
        $em = $this->getDoctrine()->getManager();
    	
        $contact = $em->getRepository('AppBundle:Contact')->find($contact_id);
    	
        $em->remove($contact);
        $em->flush();
    	
        return $this->redirectToRoute('contact_list');
    }
    
    private function addContact($contact)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($contact);
        $em->flush();
    }
    
    public function groupListAction(Request $request)
    {
        $groups = $this->getDoctrine()
        ->getRepository('AppBundle:Group')
        ->findBy(
            array(),
            array('name' => 'ASC')
        );
    	
        return $this->render(
            'AppBundle:group_list.html.twig', 
            array('groups' => $groups)
        );
    }
    
    public function createGroupAction(Request $request)
    {
    	$group = new Group();
    	
    	$form = $this->createFormBuilder(
            $group,
            array('validation_groups' => array('Create')))
        ->add('name', 'text')
        ->add('submit_button', 'submit')
        ->getForm();
    			 
        $form->handleRequest($request);
    			 
        if ($form->isValid()) {
            self::addGroup($group);
            return $this->redirect($this->generateUrl('groups'));
        }
    			
        return $this->render(
            'AppBundle:create_group.html.twig',
            array('form' => $form->createView()));
    }
    
    private function addGroup($group)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();
    }
    
    public function removeGroupAction(Request $request, $group_id)
    {
        $em = $this->getDoctrine()->getManager();
    	 
        $group = $em->getRepository('AppBundle:Group')->find($group_id);
    	 
        $em->remove($group);
        $em->flush();
    	 
        return $this->redirectToRoute('groups');
    }
}
