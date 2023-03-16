<?php

function OnlyPassPage()
{
    add_menu_page("OnlyPass Page","OnlyPass","manage_options","onlypass-page","OnlyPassPageView",plugins_url( 'onlypass-woocommerce-wp-plugin/assets/menu_logo.png' ),4);
}

function OnlyPassPageView()
{
    $list = [];
    $nolist = true;
    for($i = 0;$i < 12;$i++) {
        $list[] = array("id" => "","transactionId"=>"", "memo"=>"", "amount"=>"");
    }
    foreach ($list as $k=>$v) {
        if(!empty($v["transactionId"]))
        {
            $nolist = false;
        }
    }
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" />';
echo '<div class="content p-5" >
<h5>OnlyPass Payment history</h5>';
if($nolist) {
echo '<div class="alert alert-danger">
No transactions<br/>
There\'re no transactions for this query. Please try again later.
</div>';
}
 echo '<table class="table table-responsive" style="width: 100%; display: table;">
  <thead class="" style="background: #e9ac00;">
    <tr style="width: 100%; display: contents;">
      <th scope="col">#</th>
      <th scope="col">Transaction ID</th>
      <th scope="col">Memo</th>
      <th scope="col">Amount</th>
    </tr>
  </thead>
  <tbody>';
foreach ($list as $k=>$v) {
    echo '<tr>
      <th scope="row">'.($k+1).'</th>
      <td>'.$v["transactionId"].'</td>
      <td>'.$v["memo"].'</td>
      <td>'.$v["amount"].'</td>
    </tr>';
}
echo '</tbody>
</table>
</div>';

}
if(get_option("onlypass_api_key") != null || !empty(get_option("onlypass_api_key"))) {
    add_action("admin_menu", "OnlyPassPage");
}