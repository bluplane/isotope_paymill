<?php if (!defined("TL_ROOT")) die("You can not access this file directly!");

$GLOBALS["TL_DCA"]["tl_iso_payment_modules"]["palettes"]["paymill"] = "{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{price_legend:hide},price,tax_class;{paymillSettings_legend},paymill_public_key,paymill_private_key;{expert_legend:hide},guests,protected;{enabled_legend},enabled";
 
$GLOBALS["TL_DCA"]["tl_iso_payment_modules"]["fields"]["paymill_public_key"] = array(
	"label"						=> &$GLOBALS["TL_LANG"]["tl_iso_payment_modules"]["paymill_public_key"],
	"default"					=> "",
	"exclude"					=> true,
	"inputType"					=> "text",
	"eval"						=> array("tl_class" => "clr long")
);

$GLOBALS["TL_DCA"]["tl_iso_payment_modules"]["fields"]["paymill_private_key"] = array(
	"label"						=> &$GLOBALS["TL_LANG"]["tl_iso_payment_modules"]["paymill_private_key"],
	"default"					=> "",
	"exclude"					=> true,
	"inputType"					=> "text",
	"eval"						=> array("tl_class" => "clr long")
);

class PaymentPaymillIsoPaymentModule extends Backend {

	public function __construct() {
		parent::__construct();
	}
	
	public function reason($strValue, DataContainer $dc) {
		return utf8_romanize($strValue);
	}

}
	
?>
