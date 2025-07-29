<?php

namespace ZatcaInvoice;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class HtmlInvoiceGenerator {

    /**
     * Generate the PDF invoice.
     *
     * @param int $order_id
     * @return string|false The path to the generated PDF or false on failure.
     */
    public function generate($order_id, $lang = null) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        // Get the QR code as a data URI
        $qr_code_uri = $this->generate_qr_code_uri($order);

        // Get the HTML content for the invoice
        $html = $this->get_invoice_template($order, $qr_code_uri, $lang);

        return $html;
    }

    /**
     * Get the invoice template HTML.
     *
     * @param WC_Order $order
     * @param string $qr_code_uri
     * @param string $lang Optional. The language for the invoice.
     * @return string
     */
    private function get_invoice_template($order, $qr_code_uri, $lang = null) {
        $template_style = get_option('zatca_invoice_template_style', 'default');
        $template_path = ZATCA_INVOICE_PLUGIN_PATH . 'templates/invoice-' . $template_style . '.php';

        // Fallback to default if the selected template doesn't exist
        if (!file_exists($template_path)) {
            $template_path = ZATCA_INVOICE_PLUGIN_PATH . 'templates/invoice-template.php';
        }

        ob_start();
        include $template_path;
        return ob_get_clean();
    }

    /**
     * Generate the ZATCA compliant QR code and return it as a data URI.
     *
     * @param WC_Order $order
     * @return string
     */
    private function generate_qr_code_uri($order) {
        $seller_name = get_option('zatca_invoice_seller_name', get_bloginfo('name'));
        $vat_number = get_option('zatca_invoice_vat_number');
        $timestamp = $order->get_date_created()->format('Y-m-d\TH:i:s\Z');
        $total = $order->get_total();
        $tax_total = $order->get_total_tax();

        $tlv_string = $this->to_tlv(
            1, $seller_name,
            2, $vat_number,
            3, $timestamp,
            4, $total,
            5, $tax_total
        );

        $base64_tlv = base64_encode($tlv_string);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($base64_tlv)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        return $result->getDataUri();
    }

    /**
     * Convert data to TLV format.
     *
     * @param mixed ...$tags
     * @return string
     */
    private function to_tlv(...$tags) {
        $tlv = '';
        for ($i = 0; $i < count($tags); $i += 2) {
            $tag = $tags[$i];
            $value = (string) $tags[$i + 1];
            $length = strlen($value);
            $tlv .= pack('C', $tag) . pack('C', $length) . $value;
        }
        return $tlv;
    }
}
