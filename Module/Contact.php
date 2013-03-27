<?php
/**
 * Article page type
 * @version 1.0.0
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Contact extends \Gratheon\CMS\ContentModule {
    var $per_page = 20;
    var $name = 'contact';

    function front_view($parentID) {
        $content_image = $this->model('Image');
        $content_contact = $this->model('content_contact');
        $content_article = $this->model('content_article');
        $content_menu = $this->model('content_menu');

        $recElement = $content_contact->obj('parentID=' . $parentID);

        if ($this->controller->in->post) {
            $this->addFeedback($recElement);
        }

        $arrSubpages = (array)$content_menu->arr('module IN ("article") AND parentID=' . $parentID);
        foreach ($arrSubpages as &$subpage) {
            $subpage->subdata = $content_article->obj('parentID=' . $subpage->ID);
        }
        $recElement->subpages = $arrSubpages;

        $arrImages = (array)$content_menu->arr('module IN ("image") AND parentID=' . $parentID);
        foreach ($arrImages as &$subimage) {
            $arrImage = $content_image->obj('parentID=' . $subimage->ID);
            $arrImage->link_image = $content_image->getOriginalURL($arrImage);
            $subimage->subdata = $arrImage;
        }
        $recElement->subimages = $arrImages;

        $this->assign('element', $recElement);
        //$this->assign('content_template','module.contact.view.tpl');
    }

    function addFeedback($recElement) {
        global $user;

        if (!$this->controller->in->post['message']) {
            $arrErrors[] = $this->translate('Empty message');
        }

        if (!$this->controller->in->post['name']) {
            $arrErrors[] = $this->translate('Empty name');
        }

        if (!$this->controller->in->post['email']) {
            $arrErrors[] = $this->translate('Empty email');
        }

        $content_contact_log = $this->model('content_contact_log');
        $content_module = $this->model('content_module');
        $sys_email_templates = $this->model('sys_email_templates');

        $recLog = new \Gratheon\Core\Record();
        if (!$arrErrors) {
            if ($recElement->log_db) {
                $recLog->contactID = $recElement->ID;
                if ($user->data['ID']) {
                    $recLog->userID = $user->data['ID'];
                }
                $recLog->fullname = $this->controller->in->post['name'];
                $recLog->email = $this->controller->in->post['email'];
                $recLog->message = $this->controller->in->post['message'];
                $recLog->date_added = 'NOW()';
                $content_contact_log->insert($recLog);
                $recElement->success[] = $this->translate('Message logged successully');
            }

            if ($recElement->send_email) {

                global $system, $menu, $controller, $config;

                //$arrUser	= $sys_user->obj($userID);
                $moduleID = $content_module->int('title="' . $this->name . '"', 'ID');
                $langID = $system->langID;

                $arrTemplate = $sys_email_templates->obj("tag='contact_feedback' AND moduleID='$moduleID' AND langID='$langID'");

                $arrParams = array(
                    'subject' => $recElement->email_title,
                    'template' => 'ModuleFrontend/front.email.tpl',
                    'text' => $arrTemplate->text,
                    'html' => $arrTemplate->html,
                    'to' => $recElement->email,
                    //'to_name'	=> 'admin',
                    'from' => $config->front['email_notification_sender'],
                    'from_name' => $config->front['email_notification_sender_name'],

                    'replace' => array(
                        '{name}' => $this->controller->in->post['name'],
                        '{email}' => $this->controller->in->post['email'],
                        '{message}' => $this->controller->in->post['message'],

                        '{link_homepage}' => sys_url
                    )
                );

                $this->send_mail($arrParams);
                //mail($recElement->email,$recElement->email_title,$this->controller->in->post['message']);
                $recSuccess[] = $this->translate('Message sent successully');
                //pre($this->controller->in->post);
            }
        }


        $this->assign('element', $recElement);
        $this->assign('errors', $arrErrors);
        $this->assign('ok', $recSuccess);
    }

    function delete_log() {
        $content_contact_log = $this->model('content_contact_log');
        $content_contact = $this->model('content_contact');

        $recLog = $content_contact_log->obj('ID=' . $_GET['ID']);
        $recElement = $content_contact->obj($recLog->contactID);

        $content_contact_log->delete('ID=' . $_GET['ID']);

        $this->controller->redirect('/content/edit/?ID=' . $recElement->parentID);
    }


    function edit($recMenu = null) {
        $content_contact_log = $this->model('content_contact_log');
        $content_contact = $this->model('content_contact');

        $parentID = $recMenu->ID;

        if ($parentID) {
            $recElement = $content_contact->obj('parentID=' . $parentID);

            $offset = isset($_GET['page']) ? $this->per_page * ($_GET['page'] - 1) : 0;
            $arrList = $content_contact_log->arr('contactID=' . $recElement->ID . " ORDER BY date_added DESC LIMIT " . $offset . ',' . $this->per_page,
                "*, DATE_FORMAT(date_added,'%d.%m.%Y %h:%i') date_added_formatted");

            foreach ($arrList as &$item) {
                //$item->link_delete='/content/module_subcall/contact/delete_log/?ID='.$item->ID;
                $item->link_delete = sys_url . '/content/call/' . $this->name . '/delete_log/?ID=' . $item->ID;
            }

            #Create page navigation for first page
            $intPage = isset($_GET['page']) ? (int)$_GET['page'] : 0;
            $objPaginator = new CMS\Paginator($this->controller->in, $content_contact_log->count, $intPage, $this->per_page);
            $objPaginator->url = '/content/edit/?ID=' . $_GET['id'];

            $this->assign('recElement', $recElement);
            $this->assign('objPaginator', $objPaginator);
            $this->assign('arrList', $arrList);
        }
        $this->assign('show_URL', true);
    }

    function update($parentID) {
        $content_contact = $this->model('content_contact');

        $recElement = new \Gratheon\Core\Record();
        $recElement->email_title = $this->controller->in->post['email_title'];
        $recElement->send_email = $this->controller->in->post['send_email'];
        $recElement->email = $this->controller->in->post['email'];
        $recElement->log_db = $this->controller->in->post['log_db'];
        $content_contact->update($recElement, 'parentID=' . $parentID);
    }

    function insert($parentID) {
        $content_contact = $this->model('content_contact');

        $recElement = new \Gratheon\Core\Record();
        $recElement->parentID = $parentID;
        $recElement->email_title = $this->controller->in->post['email_title'];
        $recElement->send_email = $this->controller->in->post['send_email'];
        $recElement->email = $this->controller->in->post['email'];
        $recElement->log_db = $this->controller->in->post['log_db'];
        $content_contact->insert($recElement);
    }

    function delete($parentID) {
        $content_contact_log = $this->model('content_contact_log');
        $content_contact = $this->model('content_contact');

        $recElement = $content_contact->obj('parentID=' . $parentID);

        $content_contact_log->delete('contactID=' . $recElement->ID);
        $content_contact->delete('parentID=' . $parentID);
    }

}