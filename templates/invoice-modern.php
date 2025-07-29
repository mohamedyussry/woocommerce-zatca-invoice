<?php
$current_lang = isset($lang) ? $lang : get_option('zatca_invoice_language', 'en');
$direction = ($current_lang === 'ar') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html dir="<?php echo esc_attr($direction); ?>" lang="<?php echo esc_attr($current_lang); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html__('Tax Invoice', 'woocommerce-zatca-invoice'); ?> | <?php echo esc_html__('Invoice', 'woocommerce-zatca-invoice'); ?></title>
    <style>
        @font-face {
            font-family: 'Noto Sans Arabic';
            font-style: normal;
            font-weight: normal;
            src: url('<?php echo ZATCA_INVOICE_PLUGIN_PATH . 'vendor/endroid/qr-code/assets/noto_sans.otf'; ?>') format('opentype');
        }
        body {
            font-family: 'Noto Sans Arabic', 'DejaVu Sans', sans-serif;
            direction: <?php echo esc_attr($direction); ?>;
            text-align: <?php echo ($direction === 'rtl') ? 'right' : 'left'; ?>;
            color: #333;
            line-height: 1.6;
            font-size: 12px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .header .logo {
            max-width: 200px;
        }
        .header .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            text-align: center;
            flex-grow: 1;
        }
        .header .invoice-details {
            text-align: <?php echo ($direction === 'rtl') ? 'left' : 'right'; ?>;
        }
        .header .invoice-details p {
            margin: 0;
        }
        .section-title {
            background: #f0f8ff;
            padding: 8px 15px;
            margin-bottom: 15px;
            border-<?php echo ($direction === 'rtl') ? 'right' : 'left'; ?>: 5px solid #007bff;
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        .address-block {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .address-block > div {
            width: 48%;
            padding: 10px;
            border: 1px solid #eee;
            box-sizing: border-box;
        }
        .address-block h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #007bff;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th,
        table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            text-align: <?php echo ($direction === 'rtl') ? 'right' : 'left'; ?>;
        }
        table th {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-section {
            text-align: <?php echo ($direction === 'rtl') ? 'left' : 'right'; ?>;
            margin-top: 20px;
        }
        .total-section table {
            width: auto;
            margin-<?php echo ($direction === 'rtl') ? 'left' : 'right'; ?>: 0;
            margin-<?php echo ($direction === 'rtl') ? 'right' : 'left'; ?>: auto;
            border: none;
        }
        .total-section table td {
            border: none;
            padding: 5px 15px;
        }
        .total-section table tr:last-child td {
            font-weight: bold;
            font-size: 1.3em;
            border-top: 2px solid #007bff;
        }
        .qr-code-section {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #ddd;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 0.8em;
            color: #777;
        }
        .bilingual {
            display: block;
        }
        .bilingual .ar {
            direction: rtl;
            unicode-bidi: embed;
            font-weight: bold;
        }
        .bilingual .en {
            direction: ltr;
            unicode-bidi: embed;
            font-size: 0.9em;
            color: #555;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 5px;
            color: #fff;
            font-weight: bold;
            text-transform: capitalize;
        }
        .status-pending { background-color: #ffc107; }
        .status-processing { background-color: #17a2b8; }
        .status-on-hold { background-color: #fd7e14; }
        .status-completed { background-color: #28a745; }
        .status-cancelled { background-color: #dc3545; }
        .status-refunded { background-color: #6c757d; }
        .status-failed { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="logo">
                <?php if (get_option('zatca_invoice_logo_url')) : ?>
                    <img src="<?php echo esc_attr(get_option('zatca_invoice_logo_url')); ?>" style="max-width:100%; height:auto;">
                <?php else : ?>
                    <h1><?php echo esc_html(get_option('zatca_invoice_seller_name', get_bloginfo('name'))); ?></h1>
                <?php endif; ?>
            </div>
            <div class="invoice-title">
                <?php if ($current_lang === 'ar') : ?>
                    <span class="ar">فاتورة ضريبية</span>
                <?php else : ?>
                    <span class="en">Tax Invoice</span>
                <?php endif; ?>
            </div>
            <div class="invoice-details">
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">رقم الفاتورة:</span><?php else : ?><span class="en">Invoice No.:</span><?php endif; ?> <?php echo $order->get_order_number(); ?></p>
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">التاريخ والوقت:</span><?php else : ?><span class="en">Date & Time:</span><?php endif; ?> <?php echo $order->get_date_created()->format('Y-m-d H:i:s'); ?></p>
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">حالة الطلب:</span><?php else : ?><span class="en">Order Status:</span><?php endif; ?> <span class="order-status status-<?php echo $order->get_status(); ?>"><?php echo wc_get_order_status_name($order->get_status()); ?></span></p>
            </div>
        </div>

        <div class="address-block">
            <div>
                <h4><?php if ($current_lang === 'ar') : ?><span class="ar">معلومات المورد</span><?php else : ?><span class="en">Supplier Details</span><?php endif; ?></h4>
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">الاسم:</span><?php else : ?><span class="en">Name:</span><?php endif; ?> <?php echo esc_html(get_option('zatca_invoice_seller_name', get_bloginfo('name'))); ?></p>
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">الرقم الضريبي:</span><?php else : ?><span class="en">VAT No.:</span><?php endif; ?> <?php echo esc_html(get_option('zatca_invoice_vat_number')); ?></p>
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">السجل التجاري:</span><?php else : ?><span class="en">CR No.:</span><?php endif; ?> <?php echo esc_html(get_option('zatca_invoice_cr_number')); ?></p>
            </div>
            <div>
                <h4><?php if ($current_lang === 'ar') : ?><span class="ar">معلومات المشتري</span><?php else : ?><span class="en">Buyer Details</span><?php endif; ?></h4>
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">الاسم:</span><?php else : ?><span class="en">Name:</span><?php endif; ?> <?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></p>
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">البريد الإلكتروني:</span><?php else : ?><span class="en">Email:</span><?php endif; ?> <?php echo $order->get_billing_email(); ?></p>
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">العنوان:</span><?php else : ?><span class="en">Address:</span><?php endif; ?> <?php echo $order->get_formatted_billing_address(); ?></p>
                <?php /* Add Buyer VAT Number if available */ ?>
                <p><?php if ($current_lang === 'ar') : ?><span class="ar">الرقم الضريبي للمشتري:</span><?php else : ?><span class="en">Buyer VAT No.:</span><?php endif; ?> N/A</p>
            </div>
        </div>

        <div class="section-title"><?php if ($current_lang === 'ar') : ?><span class="ar">تفاصيل البنود</span><?php else : ?><span class="en">Item Details</span><?php endif; ?></div>
        <table>
            <thead>
                <tr>
                    <th><?php if ($current_lang === 'ar') : ?><span class="ar">المنتج</span><?php else : ?><span class="en">Product</span><?php endif; ?></th>
                    <th><?php if ($current_lang === 'ar') : ?><span class="ar">الكمية</span><?php else : ?><span class="en">Qty</span><?php endif; ?></th>
                    <th><?php if ($current_lang === 'ar') : ?><span class="ar">سعر الوحدة</span><?php else : ?><span class="en">Unit Price</span><?php endif; ?></th>
                    <th><?php if ($current_lang === 'ar') : ?><span class="ar">الضريبة</span><?php else : ?><span class="en">Tax</span><?php endif; ?></th>
                    <th><?php if ($current_lang === 'ar') : ?><span class="ar">المجموع</span><?php else : ?><span class="en">Total</span><?php endif; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order->get_items() as $item_id => $item) : ?>
                    <tr>
                        <td><?php echo $item->get_name(); ?></td>
                        <td><?php echo $item->get_quantity(); ?></td>
                        <td><?php echo wc_price($item->get_product()->get_price()); ?></td>
                        <td><?php echo wc_price($item->get_total_tax()); ?></td>
                        <td><?php echo wc_price($item->get_total() + $item->get_total_tax()); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-section">
            <table>
                <tr>
                    <td><?php if ($current_lang === 'ar') : ?><span class="ar">الإجمالي قبل الضريبة:</span><?php else : ?><span class="en">Subtotal:</span><?php endif; ?></td>
                    <td><?php echo wc_price($order->get_subtotal()); ?></td>
                </tr>
                <tr>
                    <td><?php if ($current_lang === 'ar') : ?><span class="ar">قيمة الضريبة المضافة:</span><?php else : ?><span class="en">VAT Amount:</span><?php endif; ?></td>
                    <td><?php echo wc_price($order->get_total_tax()); ?></td>
                </tr>
                <tr>
                    <td><?php if ($current_lang === 'ar') : ?><span class="ar">إجمالي الفاتورة (شامل الضريبة):</span><?php else : ?><span class="en">Total Invoice Amount (Incl. VAT):</span><?php endif; ?></td>
                    <td><?php echo $order->get_formatted_order_total(); ?></td>
                </tr>
            </table>
        </div>

        <div class="qr-code-section">
            <img src="<?php echo $qr_code_uri; ?>" style="width: 150px; height: 150px;">
            <p><?php if ($current_lang === 'ar') : ?><span class="ar">امسح للتحقق</span><?php else : ?><span class="en">Scan to verify</span><?php endif; ?></p>
        </div>

        <div class="footer">
            <p><?php if ($current_lang === 'ar') : ?><span class="ar">معلومات المورد:</span><?php else : ?><span class="en">Supplier Info:</span><?php endif; ?> <?php echo esc_html(get_option('zatca_invoice_seller_name', get_bloginfo('name'))); ?> | <?php if ($current_lang === 'ar') : ?><span class="ar">الرقم الضريبي:</span><?php else : ?><span class="en">VAT No.:</span><?php endif; ?> <?php echo esc_html(get_option('zatca_invoice_vat_number')); ?></p>
            <p><?php if ($current_lang === 'ar') : ?><span class="ar">شكراً لتعاملكم معنا!</span><?php else : ?><span class="en">Thank you for your business!</span><?php endif; ?></p>
        </div>
    </div>
</body>
</html>