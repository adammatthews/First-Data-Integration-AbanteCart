<form action="<?php echo str_replace('&', '&amp;', $action); ?>" method="post" id="checkout">

<?php 
	//$dateTime = date("Y:m:d-H:i:s");
     function getDateTime() {
           //global $dateTime;
     		date_default_timezone_set('Europe/London');
           return date("Y:m:d-H:i:s");
	}
	function createHash($chargetotal, $currency) { 
		$storeId = "1107516466";
		$sharedSecret = "UvSLSWwi5X";
		$stringToHash = $storeId . getDateTime() . $chargetotal . $currency . $sharedSecret;
		//echo "<p>".$stringToHash."</p>";
        $ascii = bin2hex($stringToHash);
        return sha1($ascii);
     }

     print_r($display_totals);
?>
	
	<input type="hidden" name="txntype" value="sale">
	<input type="hidden" name="timezone" value="GMT"/>
	<input type="hidden" name="txndatetime" value="<?php echo getDateTime(); ?>"/>
	<input type="hidden" name="hash" value="<?php echo createHash( $order_total,"826" ) ?>"/>
	<input type="hidden" name="storename" value="1107516466"/>
	<input type="hidden" name="mode" value="payonly"/> 
	<input type="hidden" name="chargetotal" value="<?php echo $order_total;?>"/> 
	<input type="hidden" name="currency" value="826"/>
	<input type="hidden" name="oid" value="<?php echo $oid; ?>"/>
	<input type="hidden" name="transactionNotificationURL" value="<?php echo $notify_url; ?>"/>	￼
	<input type="hidden" name="responseSuccessURL" value="<?php echo $return; ?>"/>
	<input type="hidden" name="responseFailURL" value="<?php echo $cancel_return; ?>"/>

</form>
<div class="buttons">
	<table>
		<tr>
			<td align="left"><a onclick="location = '<?php echo str_replace('&', '&amp;', $back); ?>'"
								class="btn_standard" style="text-transform: uppercase; 	font: normal 12px Arial, Helvetica, sans-serif;
	height: 14px;
	line-height: 14px;
	padding: 5px 6px 5px 15px;"><span><?php echo $button_back; ?></span></a></td>
			<td align="right"><a onclick="$('#checkout').submit();"
								 class="btn_standard" style="text-transform: uppercase; 	font: normal 12px Arial, Helvetica, sans-serif;
	height: 14px;
	line-height: 14px;
	padding: 5px 6px 5px 15px;"8><span><?php echo $button_confirm; ?></span></a></td>
		</tr>
	</table>
</div>