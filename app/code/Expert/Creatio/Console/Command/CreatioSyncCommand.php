<?php
namespace Expert\Creatio\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;
use Expert\Creatio\Model\Creatio;
use Symfony\Component\Console\Input\InputArgument;

class CreatioSyncCommand extends Command
{
    protected function configure()
    {
        $this->setName('creatio:sync')
            ->setDescription('Sync Feeds')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    public function getInputList()
    {
        return [
            new InputArgument(
                'schedule',
                InputArgument::REQUIRED,
                'Specify Mintues'
            ),
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schedule = $input->getArgument('schedule');

        $schedule = $schedule < 10 ? 12 : $schedule + 2; 

        $objectManager = ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

        $helper = $objectManager->get('Expert\Creatio\Helper\Data');

        $auth_url = $helper->getSystemConfig('general/url');
        $auth_username = $helper->getSystemConfig('general/username');
        $auth_password = $helper->getSystemConfig('general/password');

        $customerType = $helper->getSystemConfig('customer/type');
        $contactLeadType = $helper->getSystemConfig('contact/type');
        $testimonialLeadType = $helper->getSystemConfig('testimonial/type');
        $ordersource = $helper->getSystemConfig('order/source');
        $orderprefix = $helper->getSystemConfig('order/prefix');

        $connection = $resource->getConnection();

        $creatio = new Creatio($auth_url, $auth_username, $auth_password);


        //Read Contact Entries
        $contacts = $resource->getTableName('contacts');
        $queries = $connection->fetchAll("Select * FROM $contacts WHERE `creatio_id` IS NULL OR `creatio_id` = ''");
        foreach ($queries as $query)
        {
            if(empty($query['email'])) continue;

            $Id = $creatio->getContact($query['email']);

            if($Id === false) //Create Contact IF email not exists in CRM
            {
                $Id = $creatio->create([
                    "Name" => $query['name'],
                    "Type" => $customerType,
                    "Email" => $query['email'],
                    "Phone" => $query['phone'],
                    "UsrContactStatus" => "eb5a165e-bd19-4ff9-88b3-55f973cb01cd",
                ], 'Contact');
            }
            //Create Lead
            if($Id !== false){
                $leadId = $creatio->create([
                    "LeadType" => $contactLeadType,
                    "Contact" => $query['name'],
                    "Email" => $query['email'],
                    "MobilePhone" => $query['phone'],
                    "Commentary" => $query['message'],
                    "Zip" => $query["postal"],
                    "Address" => "Zip:" . $query["postal"],
                    "Owner" => $Id,
                ], 'Lead');

                if(!empty($query["postal"])){
                    $creatio->create([
                        "Address" => "Zip:" . $query["postal"],
                        "Zip" => $query["postal"],
                        "Lead" => $leadId,
                    ], 'LeadAddress');
                }

                //SET creatio_id
                $connection->query("UPDATE $contacts SET `creatio_id` = '$leadId' WHERE `id` = {$query['id']}");
            }
        }

        //Read Testimonial Entries
        $ytestimonial = $resource->getTableName('yereone_testimonials');
        $queries = $connection->fetchAll("Select * FROM $ytestimonial WHERE `creatio_id` IS NULL OR `creatio_id` = ''");
        foreach ($queries as $query)
        {
            if(empty($query['email'])) continue;

            $Id = $creatio->getContact($query['email']);

            if($Id === false) //Create Contact IF email not exists in CRM
            {
                $Id = $creatio->create([
                    "Name" => $query['author'],
                    "Type" => $customerType,
                    "Email" => $query['email'],
                    "Phone" => $query['telephone'],
                    "UsrContactStatus" => "eb5a165e-bd19-4ff9-88b3-55f973cb01cd",
                ], 'Contact');
            }

            //Create Lead
            if($Id !== false){
                $leadId = $creatio->create([
                    "LeadType" => $testimonialLeadType,
                    "Contact" => $query['author'],
                    "Email" => $query['email'],
                    "MobilePhone" => $query['telephone'],
                    "Commentary" => $query['testimonial_content'],
                    "Owner" => $Id,
                ], 'Lead');

                //SET creatio_id
                $connection->query("UPDATE $ytestimonial SET `creatio_id` = '$leadId' WHERE `id` = {$query['id']}");
            }
        }

        //Read Newsletter Subscriber Entries
        $ntable = $resource->getTableName('newsletter_subscriber');
        $queries = $connection->fetchAll("Select * FROM $ntable  WHERE `customer_id` = 0 AND TIMESTAMPDIFF(MINUTE, `change_status_at` , now()) <= $schedule;");
        foreach ($queries as $query)
        {
            if(empty($query['subscriber_email'])) continue;
            $Id = $creatio->getContact($query['subscriber_email']);
            if($Id === false)
            {
                $Id = $creatio->create([
                    "Name" => $query['subscriber_name'],
                    "Type" => $customerType,
                    "Email" => $query['subscriber_email'],
                    "UsrContactStatus" => "eb5a165e-bd19-4ff9-88b3-55f973cb01cd",
                ], 'Contact');
            }
        }

        //Read Customers & Create if not exists
        $customer_entity = $resource->getTableName('customer_entity');
        $customer_address_entity = $resource->getTableName('customer_address_entity');
        $queries = $connection->fetchAll("SELECT 
                    A.`firstname`, A.`lastname`, A.`email`,  
                    B.`telephone`, B.`street`,  B.`country_id`,  B.`postcode`
                FROM $customer_entity A LEFT JOIN $customer_address_entity B ON A.`default_billing` = B.`entity_id`
                WHERE TIMESTAMPDIFF(MINUTE, A.`updated_at` , now()) <= $schedule ||  TIMESTAMPDIFF(MINUTE, B.`updated_at` , now()) <= $schedule;
            ");

        foreach ($queries as $query)
        {
            if(empty($query['email'])) continue;

            $Id = $creatio->getContact($query['email']);

            $data = [
                "Name" => $query['firstname'] . ' ' . $query['lastname'],
                "Type" => $customerType,
                "Email" => $query['email'],
                "Phone" => $query['telephone'],
                "Address" => $query['street'],
                "Zip" => $query['postcode'],
                "Country" => $creatio->getCountryIdByCode($query['country_id']),
            ];

            if($Id === false)
            {
                $Id = $creatio->create($data, 'Contact');
            }else{
                $Id = $creatio->updateContact($data);
            }
        }



        //Create Orders
        $sales_order = $resource->getTableName('sales_order');
        $sales_order_address = $resource->getTableName('sales_order_address');
        $sales_order_payment = $resource->getTableName('sales_order_payment');
        $sales_order_item = $resource->getTableName('sales_order_item');


        $orders = $connection->fetchAll("SELECT 
                        A.`entity_id`, A.`increment_id`, A.`customer_email`, 
                        A.`customer_firstname`, A.`customer_lastname`, 
                        A.`grand_total`,
                        B.`region_id`,B.`postcode`,B.`street`,B.`city`,
                        B.`telephone`,B.`country_id`,
                        C.`method` as `payment_method`
                        FROM $sales_order A
                        LEFT JOIN $sales_order_address B ON A.`shipping_address_id` = B.`entity_id`
                        LEFT JOIN $sales_order_payment C ON A.`entity_id` = C.`parent_id`
                        WHERE TIMESTAMPDIFF(MINUTE, `created_at` , now()) <= $schedule
                        ;                
                    ");
        foreach ($orders as $order)
        {

            $orderId = $creatio->getOrderIdyByNumber($orderprefix . $order['increment_id']);
            if($orderId) continue;


            $Id = $creatio->getContact($order['customer_email']);

            if($Id === false)
            {
                $Id = $creatio->create([
                    "Name" => $order['customer_firstname'],
                    "Type" => $customerType,
                    "Email" => $order['customer_email'],
                    "Phone" => $order['telephone'],
                    "UsrContactStatus" => "eb5a165e-bd19-4ff9-88b3-55f973cb01cd",
                ], 'Contact');
            }

            $orderId = $creatio->create([
                'Number' => $orderprefix . $order['increment_id'],
                'Owner' => $Id,
                'Contact' => $Id,
                'PaymentType' => $creatio->getPaymentTypeByCode($order['payment_method']),
                //'DeliveryType' => $creatio->getDeliveryTypeByCode($order['payment_method']),
                //'OrderChannel' => $Id, // ORDE CHANNEL
                'Amount' => $order['grand_total'],
                'PaymentAmount' => $order['grand_total'],
                'DeliveryAddress' => sprintf("%s %s, %s %s",
                                            $order['street'],
                                            $order['city'],
                                            $order['postcode'],
                                            $order['country_id']
                                    ),
                'SourceOrder' => $ordersource,
            ], 'Order');


            $products = $connection->fetchAll("SELECT * FROM $sales_order_item WHERE `order_id` = {$order['entity_id']}");

            foreach ($products as $product)
            {
                $product_id = $creatio->getProductIdyByCode($product['sku']);
                if($product_id)
                {
                    $creatio->create([
                        'Order' => $orderId,
                        'Product' => $product_id,
                        'Quantity' => $product['qty_ordered'],
                        'Price' => $product['price'],
                        'Amount' => $product['row_total'],
                        'TotalAmount' => $product['row_total'],
                    ], 'OrderProduct');
                }
                           
            }
        }
    }

}
