<?php

/** 
* This is the internal function where the API requests made to get order details from Shopee API.
*
* Request parameters varies on the update from Shopee API Documentation
* 
* File type: PHP
* Request type: CURL PHP
* 
*/

class ShopeeIntegrate extends ShopeeIntegrate_Do
{
	public function fetch_orderdetails( $username, $password, $sign, $partner_id, $shopid, $pageoffset, $pagination_per_page, $datenow, $from, $to, $lastkey_parent ){
		$this->fetch_orderdetails_x( $username, $password, $sign, $partner_id, $shopid, $pageoffset, $pagination_per_page, $datenow, $from, $to, $lastkey_parent );
	}
}

class ShopeeIntegrate_Do
{
	protected function fetch_orderdetails_x( $username, $password, $sign, $partner_id, $shopid, $pageoffset, $pagination_per_page, $datenow, $from, $to, $lastkey_parent ){
        
        /**
        * Request list of Shopee Orders based on the $post parameters.
        * Hashing the parameters is required.
        * 
        */

        $post = '{
            "partner_id"                  : '.$partner_id.', 
            "shopid"                      : '.$shopeeid.', 
            "timestamp"                   : '.$datenow.', 
            "pagination_offset"           : '.$pageoffset.', 
            "pagination_entries_per_page" : '.$pagination_per_page.',
            "create_time_from"            : '.$from.',
            "create_time_to"              : '.$to.',
            "update_time_from"            : 0,
            "update_time_to"              : 0
        }';
        $url = "https://partner.shopeemobile.com/api/v1/orders/basics";     //Shopee API url for requesting orders
        $base_string = $url."|".$post;                                      //Generate a signature base string for POST
        $signature = hash_hmac( 'sha256', $base_string, $sign );            //Generate a unique signature
        
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_POST,1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
        curl_setopt( $ch, CURLOPT_USERPWD, "$username:$password" );
        curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Authorization:".$signature, "Content-Type: application/json" ) );  
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $result = curl_exec( $ch );
        $result = json_decode( $result );
        curl_close( $ch );

        
        /**
        * Get each Order SN based on $result.
        * Send another request through the API to get the Details of each Order SN.
        *
        */

        foreach( $result->orders as $key => $value ){
            $ordersn         = $value->ordersn;                         //Order Serial Number or OrderSN
            $array_ordersn[] = '"'.$ordersn.'"';
            $lastkey         = $key;
        }
        $comma_separated     = implode( ",", $array_ordersn );          //Implode results
        
        $post = '{
            "partner_id"   : '.$partner_id.', 
            "shopid"       : '.$shopeeid.', 
            "timestamp"    : '.$datenow.', 
            "ordersn_list" : ['.$comma_separated .']
        }';
        $url = "https://partner.shopeemobile.com/api/v1/orders/detail"; //Shopee API url for requesting orders details
        $base_string = $url."|".$post;                                  //Generate a signature base string for POST
        $signature = hash_hmac( 'sha256', $base_string, $sign );        //Generate a unique signature
        
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_POST,1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
        curl_setopt( $ch, CURLOPT_USERPWD, "$username:$password" );
        curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Authorization:".$signature, "Content-Type: application/json" ) );  
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $resultx = curl_exec( $ch );
        $resultx = json_decode( $resultx );
        curl_close( $ch );

        
        /**
        * Generate results in JSON format.
        * Check each variable if NULL or empty
        * 
        */

        foreach( $resultx->orders as $keyxx => $valuexx ){

            if ( $valuexx->ordersn != "" )
                $x_order_id = $valuexx->ordersn;
            else
                $x_order_id = "";
        
            if ( $valuexx->order_status != "" ){
                if ( $valuexx->order_status == "UNPAID" ) 
                    $x_order_status = "Unpaid";
                elseif ( $valuexx->order_status == "READY_TO_SHIP" )
                    $x_order_status = "To ship";
                elseif ( $valuexx->order_status == "SHIPPED" || $valuexx->order_status == "TO_CONFIRM_RECEIVE" || $valuexx->order_status == "TO_RETURN" || $valuexx->order_status == "RETRY_SHIP" )
                    $x_order_status = "Shipping";
                elseif ( $valuexx->order_status == "CANCELLED" || $valuexx->order_status == "INVALID" || $valuexx->order_status == "IN_CANCEL" )
                    $x_order_status = "Cancelled";
                elseif ( $valuexx->order_status == "COMPLETED" )
                    $x_order_status = "Completed";
            }
            else
                $x_order_status = "";
            
            if ( $valuexx->create_time != "" )
                $x_orderdate = $valuexx->create_time;
            else
                $x_orderdate = "";

            if ( $valuexx->estimated_shipping_fee != "" )
                $x_shippingfeepaid = $valuexx->estimated_shipping_fee;
            else
                $x_shippingfeepaid = "";

            if ( $valuexx->escrow_amount != "" )
                $x_pricepaid = $valuexx->escrow_amount - $x_shippingfeepaid ;
            else
                $x_pricepaid = "";

            $x_totalamountpaid = $valuexx->escrow_amount;
            
            $product_infotemp = "";
            $product_infocounter = 1;
            foreach( $valuexx->items as $keyxxx => $valuexxx ){
                $product_infotemp.= "[".$product_infocounter."] Product Name: ".$valuexxx->item_name."; Variation Name: ".$valuexxx->variation_name."; Price: ₱".$valuexxx->variation_discounted_price."; Quantity: ".$valuexxx->variation_quantity_purchased.";";
                $product_infocounter++;
            }
            $x_productinfo = $product_infotemp;

            if ( $valuexx->recipient_address->full_address != "" )
                $x_deliveryaddress = $valuexx->recipient_address->full_address;
            else
                $x_deliveryaddress = "";
            
            if ( $valuexx->recipient_address->country != "" )
                $x_country = $valuexx->recipient_address->country;
            else
                $x_country = "";

            if ( $valuexx->recipient_address->state != "" )
                $x_region = $valuexx->recipient_address->state;
            else
                $x_region = "";

            if ( $valuexx->recipient_address->city != "" )
                $x_province = $valuexx->recipient_address->city;
            else
                $x_province = "";

            if ( $valuexx->recipient_address->zipcode != "" )
                $x_zipcode = $valuexx->recipient_address->zipcode;
            else
                $x_zipcode = "";

            if ( $valuexx->recipient_address->name != "" )
                $x_receivername = $valuexx->recipient_address->name;
            else
                $x_receivername = "";

            if ( $valuexx->recipient_address->phone != "" )
                $x_phonenumber = $valuexx->recipient_address->phone;
            else
                $x_phonenumber = "";

            if ( $valuexx->shipping_carrier != "" )
                $x_shippingoption = $valuexx->shipping_carrier;
            else
                $x_shippingoption = "";

            if ( $valuexx->tracking_no != "" ){
                $x_trackingnumber = $valuexx->tracking_no;
            }
            else{
                $x_trackingnumber = "";
                if ( $x_shippingoption == "Ninja Van" )
                    $x_trackingnumber = $x_order_id;
            }

            if ( $valuexx->update_time != "" )
                $x_ordercomplete = $valuexx->update_time;
            else
                $x_ordercomplete = "";

            if ( $valuexx->message_to_seller != "" )
                $x_buyerremark = $valuexx->message_to_seller;
            else
                $x_buyerremark = ""; 

            if ( $valuexx->note != "" )
                $x_note = $valuexx->note;
            else
                $x_note = "";

            if ( $valuexx->total_amount != "" )
                $x_deductedtotal = $valuexx->total_amount;
            else
                $x_deductedtotal = "";

            if ( $valuexx->cod == "1" )
                $x_codstatus = "1";
            else
                $x_codstatus = "0";


            /**
            * Get escrow details for each order
            * Send another request through the API to get the escrow details by ordersn.
            *
            */

            $postx = '{
                "partner_id" : '.$partner_id.', 
                "shopid"     : '.$shopeeid.', 
                "timestamp"  : '.$datenow.', 
                "ordersn"    : "'.$x_order_id.'"
            }';
            $urlx = "https://partner.shopeemobile.com/api/v1/orders/my_income"; //Shopee API url for requesting orders details
            $base_stringx = $urlx."|".$postx;                                   //Generate a signature base string for POST
            $signaturex = hash_hmac( 'sha256', $base_stringx, $sign );          //Generate a unique signature
            
            $chx = curl_init();
            curl_setopt( $chx, CURLOPT_URL, $urlx );
            curl_setopt( $chx, CURLOPT_POST,1 );
            curl_setopt( $chx, CURLOPT_POSTFIELDS, $postx );
            curl_setopt( $chx, CURLOPT_USERPWD, "$username:$password" );
            curl_setopt( $chx, CURLOPT_HTTPAUTH, CURLAUTH_ANY );
            curl_setopt( $chx, CURLOPT_HTTPHEADER, array( "Authorization:".$signaturex, "Content-Type: application/json" ) );  
            curl_setopt( $chx, CURLOPT_RETURNTRANSFER, 1 );
            $resultxx = curl_exec( $chx );
            $resultxx = json_decode( $resultxx );
            curl_close( $chx );

            if ( $resultxx->order->income_details->total_amount != "" )
                $x_escrow_totalamount = $resultxx->order->income_details->total_amount;
            else
                $x_escrow_totalamount = "";

            if ( $resultxx->order->income_details->coin != "" )
                $x_escrow_coin = $resultxx->order->income_details->coin;
            else
                $x_escrow_coin = "";

            if ( $resultxx->order->income_details->voucher != "" )
                $x_escrow_promotion = $resultxx->order->income_details->voucher;
            else
                $x_escrow_promotion = "";

            if ( $resultxx->order->income_details->voucher_seller != "" )
                $x_escrow_voucher = $resultxx->order->income_details->voucher_seller;
            else
                $x_escrow_voucher = "";

            if ( $resultxx->order->income_details->seller_rebate != "" )
                $x_escrow_rebate = $resultxx->order->income_details->seller_rebate;
            else
                $x_escrow_rebate = "";

            if ( $resultxx->order->income_details->shipping_fee_rebate != "" )
                $x_escrow_shiprebate = $resultxx->order->income_details->shipping_fee_rebate;
            else
                $x_escrow_shiprebate = "";

            if ( $resultxx->order->income_details->commission_fee != "" )
                $x_escrow_shopeecommission = $resultxx->order->income_details->commission_fee;
            else
                $x_escrow_shopeecommission = "";

            if ( $resultxx->order->income_details->voucher_code != "" )
                $x_escrow_vouchercode = $resultxx->order->income_details->voucher_code;
            else
                $x_escrow_vouchercode = "";

            if ( $resultxx->order->income_details->voucher_name != "" )
                $x_escrow_vouchername = $resultxx->order->income_details->voucher_name;
            else
                $x_escrow_vouchername = "";

            if ( $resultxx->order->income_details->escrow_amount != "" )
                $x_escrow_amount = $resultxx->order->income_details->escrow_amount;
            else
                $x_escrow_amount = "";

            /**
            * Generate JSON
            * 
            * return @param $callback_result A variable the stores the order details in json.
            *
            * $order_id - Order Number
            * $order_status - Order Status
            * $orderdate - Order Date
            * $shippingfeepaid - Shipping Fee
            * $pricepaid - Price Paid
            * $totalamountpaid - Total Amount Paid
            * $productinfo - Product Information
            * $deliveryaddress - Delivery Address
            * $country - Country
            * $region - Region
            * $province - Province
            * $zipcode - Zipcode
            * $receivername - Receiver Name
            * $phonenumber - Phone Number
            * $shippingoption - Shipping Option
            * $trackingnumber - Tracking Number
            * $ordercomplete - Order Complete Time
            * $buyerremark - Buyer Remarks
            * $note - Note
            * $deductedtotal - Deducted Price Total Amount (with shopee coins deduction)
            * $codstatus - COD Status
            * $escrow_totalamount - Escrow Total Amount
            * $escrow_coin - Escrow Shopee Coin Amount
            * $escrow_promotion - Escrow Promotion Amount
            * $escrow_voucher - Escrow Voucher
            * $escrow_rebate - Escrow Seller Rebate
            * $escrow_shiprebate - Escrow Ship Rebate
            * $escrow_shopeecommission - Escrow Shopee Commission Fee
            * $escrow_vouchercode - Escrow Voucher Code
            * $escrow_vouchername - Escrow Voucher Name
            * $escrow_amount - Escrow Amount
            *
            */

            $callback_result = '{ 
                "order_id" => "'.$x_order_id.'",
                "order_status" => "'.$x_order_status.'",
                "orderdate" => "'.$x_orderdate.'",
                "pricepaid" => $x_pricepaid,
                "shippingfeepaid" => "'.$x_shippingfeepaid.'",
                "totalamountpaid" => "'.$x_totalamountpaid.'",
                "productinfo" => "'.$x_productinfo.'",
                "deliveryaddress" => "'.$x_deliveryaddress.'",
                "country" => "'.$x_country.'",
                "region" => "'.$x_region.'",
                "province" => "'.$x_province.'",
                "zipcode" => "'.$x_zipcode.'",
                "receivername" => "'.$x_receivername.'",
                "phonenumber" => "'.$x_phonenumber.'",
                "shippingoption" => "'.$x_shippingoption.'",
                'trackingnumber' => $x_trackingnumber,
                'ordercomplete' => $x_ordercomplete,
                'buyerremark' => $x_buyerremark,
                'note' => $x_note,
                'deductedtotal' => $x_deductedtotal,
                'codstatus' => $x_codstatus,
                'escrow_totalamount' => $x_escrow_totalamount,
                'escrow_coin' => $x_escrow_coin,
                'escrow_promotion' => $x_escrow_promotion,
                'escrow_voucher' => $x_escrow_voucher,
                'escrow_rebate' => $x_escrow_rebate,
                'escrow_shiprebate' => $x_escrow_shiprebate,
                'escrow_shopeecommission' => $x_escrow_shopeecommission,
                'escrow_vouchercode' => $x_escrow_vouchercode,
                'escrow_vouchername' => $x_escrow_vouchername,
                'escrow_amount' => $x_escrow_amount
                }";
            
            
            sleep(1);
        }
            
    }
}