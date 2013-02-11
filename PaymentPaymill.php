<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
 
class PaymentPaymill extends IsotopePayment {

	public function checkoutForm() {

error_reporting(E_ALL);
		$this->import('Isotope');
		
		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
			$this->redirect($this->addToUrl('step=failed', true));
		
		$strCountryId	= strtoupper($this->Isotope->Cart->billingAddress->country);
				
		$objTemplate = new FrontendTemplate("iso_payment_paymill");

		$objTemplate->action = $this->Environment->base . $this->Environment->request;
  		$objTemplate->paymill_public_key = $this->paymill_public_key;
				
		return $objTemplate->parse();

	}
	
	
	public function processPayment() {

		$objOrder = new IsotopeOrder();
		
		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
			return false;

		if ($objOrder->date_paid > 0 && $objOrder->date_paid <= time()) {

			IsotopeFrontend::clearTimeout();
			return true;

		}

		if (IsotopeFrontend::setTimeout()) {

			global $objPage;
			$objPage->noSearch = 1;
			$objPage->cache = 0;

			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'processing';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];

			return $objTemplate->parse();

		}

		$this->log('Payment could not be processed.', __METHOD__, TL_ERROR);
		
		$this->redirect($this->addToUrl('step=failed', true));

	}
	
	public function processPostSale() {

		$intOrderId = $this->Input->get("paymillToken");
 
        if ($token = $_POST['paymillToken']) {
  
            require(TL_ROOT . '/system/modules/isotope_paymill/paymill/Transactions.php');
            $transactionsObject = new Services_Paymill_Transactions($this->paymill_private_key, 'https://api.paymill.com/v2/');
 
            $params = array(
            'amount'        => '15',   // E.g. "15" for 0.15 EUR!
            'currency'      => 'EUR',  // ISO 4217
            'token'         => $token,
            'description'   => 'Test Transaction'
            );
 
            $transaction = $transactionsObject->create($params);
 
        }

        if ($transaction['id']) {

			$this->log("Order " . $intOrderId . " successfully payed (hashes match).", "PaymentPaymill processPostSale()", TL_GENERAL);
			
			$this->Database->prepare("UPDATE tl_iso_orders SET date_paid = ? WHERE id = ?")->execute(time(), $intOrderId);
			
			header('HTTP/1.1 200 OK');
			exit;

		} else {
			
			$this->log('Payment could not be processed.', __METHOD__, TL_ERROR);
			
			$this->redirect($this->addToUrl('step=failed', true));

		}
		
	}
	
	public function backendInterface($intOrderId) {

		$objOrder = $this->Database->prepare("SELECT date_paid FROM tl_iso_orders WHERE id = ?")->execute($intOrderId);
		
		$objTemplate = new BackendTemplate("be_paymill");
		
		$objTemplate->href	= $this->getReferer(true);
		$objTemplate->title	= specialchars($GLOBALS['TL_LANG']['MSC']['backBT']);
		$objTemplate->link	= $GLOBALS['TL_LANG']['MSC']['backBT'];
		$objTemplate->h2	= sprintf($GLOBALS['TL_LANG']['ISO']['paymillH2'], $intOrderId);
		$objTemplate->label	= $GLOBALS['TL_LANG']['ISO']['paymillLabel'];
		$objTemplate->text	= strlen($objOrder->date_paid) ? sprintf($GLOBALS['TL_LANG']['ISO']['paymillText']['done'], $this->parseDate($GLOBALS["TL_CONFIG"]["dateFormat"], $objOrder->date_paid), $this->parseDate($GLOBALS["TL_CONFIG"]["timeFormat"], $objOrder->date_paid)) : $GLOBALS["TL_LANG"]["ISO"]["paymillText"]["open"];
		
		return $objTemplate->parse();

	}

}
