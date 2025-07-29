<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('تفعيل الترخيص مطلوب', 'woocommerce-zatca-invoice'); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background-color: #f0f0f1;
            color: #444;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            direction: rtl; /* Right-to-left for Arabic */
            text-align: right; /* Align text to the right */
        }
        .container {
            background-color: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 30px;
            max-width: 600px;
            text-align: center;
            border-radius: 5px;
        }
        h1 {
            color: #d63638;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 15px;
            font-size: 16px;
        }
        .whatsapp-button {
            display: inline-block;
            background-color: #25D366;
            color: #fff;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        .whatsapp-button:hover {
            background-color: #1DA851;
        }
        .developer-link {
            margin-top: 15px;
            display: block;
            color: #007cba;
            text-decoration: none;
            font-size: 14px;
        }
        .developer-link:hover {
            text-decoration: underline;
        }
        .back-link {
            margin-top: 20px;
            display: block;
            color: #007cba;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php _e('تفعيل ترخيص الإضافة مطلوب', 'woocommerce-zatca-invoice'); ?></h1>
        <p><?php echo $message; ?></p>
        <p>
            <a href="<?php echo esc_url($whatsapp_url); ?>" class="whatsapp-button" target="_blank">
                <?php _e('التواصل معنا عبر الواتساب', 'woocommerce-zatca-invoice'); ?>
            </a>
        </p>
        <a href="<?php echo esc_url($developer_url); ?>" class="developer-link" target="_blank">
            <?php _e('زيارة صفحة المطور', 'woocommerce-zatca-invoice'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url()); ?>" class="back-link">
            <?php _e('&larr; العودة إلى لوحة التحكم', 'woocommerce-zatca-invoice'); ?>
        </a>
    </div>
</body>
</html>
