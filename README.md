# Fetch Order Details in SHOPEE API in PHP
- Version: 1.0
- Language: PHP
- Developer: Lemuel

This php file will help you fetch orders, every 15 days, with details from your Shopee Online Store and give you results in JSON.
You can either create a CRON job (20mins interval suggested) or create a function that will run `cron_fetchorder.php`

## Update the `config.php` file and insert the correct details:
- **Username**
- **Password**
- **Sign Key**
- **Partner ID**
- **Shop ID**

The default number of days of orders, starting from the current time, being fetch is 99 days. The script will loop 7 times, in each loop is getting 15 days worth of orders in your shop. This will help your system be updated on their delivery and order status.

This function works by running `cron_fetchorder.php`.

### What are the data you will get:
- `$order_id` Shopee's unique identifier for an order.
- `$order_status` Enumerated type that defines the current status of the order. Order Statuses: UNPAID, READY_TO_SHIP, RETRY_SHIP, SHIPPED, TO_CONFIRM_RECEIVE, IN_CANCEL, CANCELLED, TO_RETURN, COMPLETED
- `$orderdate` Timestamp that indicates the last time that there was a change in value of order, such as order status changed from 'Paid' to 'Completed'.   
- `$shippingfeepaid` The estimated shipping fee is an estimation calculated by Shopee based on specific logistics courier's standard.
- `$pricepaid` Escrow Amount minus the Shipping Fee paid.
- `$totalamountpaid` Total Escrow Amount
- `$productinfo` Item name and Variation
- `$deliveryaddress` Complete Shipping Address
- `$country` Country
- `$region` Region
- `$province` Province
- `$zipcode` Zipcode
- `$receivername` Recipient's name for the address.
- `$phonenumber` Phone Number
- `$shippingoption` The logistics service provider that the buyer selected for the order to deliver items.
- `$trackingnumber` The tracking number assigned by the shipping carrier for item shipment.
- `$ordercomplete` Order Complete Time
- `$buyerremark` Buyer Remarks
- `$note` Note
- `$deductedtotal` Deducted Price Total Amount (with shopee coins deduction)
- `$codstatus` COD Status
- `$escrow_totalamount` The total amount paid by the buyer for the order. This amount includes the total sale price of items, shipping cost beared by buyer; and offset by Shopee promotions if applicable.
- `$escrow_coin` Final value of coins used by seller for the order.
- `$escrow_promotion` Final value of voucher provided by Shopee for the order.
- `$escrow_voucher` Final value of voucher provided by Seller for the order.
- `$escrow_rebate` Final sum of each item Shopee discount of a specific order. This amount will rebate to seller.
- `$escrow_shiprebate` The platform shipping subsidy to the seller.
- `$escrow_shopeecommission` The commission fee charged by Shopee platform if applicable.
- `$escrow_vouchercode` The voucher code or promotion code the buyer used.
- `$escrow_vouchername` The voucher name or promotion name the buyer used.
- `$escrow_amount` The total amount that the seller is expected to receive for the order and will change before order completed. `escrow_amount=total_amount+voucher+credit_card_promotion+seller_rebate+coin-commission_fee-credit_card_transaction_fee-cross_border_tax-service_fee-buyer_shopee_kredit-seller_coin_cash_back+final_shipping_fee-seller_return_refund_amount.`

The data above will be presented in JSON format. You can also insert your own PHP code in the cron_fetchorder.php or function.php if you want to save the data directly to your database.

You can check SHOPEE OPEN DOCS for more info about the API: https://open.shopee.com/documents
