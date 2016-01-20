<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
        <form id="form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-everypay" class="form-horizontal">
            <div id="tab-general" class="page">
                <table class="form">
                    <tr>
                        <td>
                            <label for="input-public-key"><?php echo $entry_public_key; ?>: <span class="help"><?php echo $help_key_id; ?></span></label>
                        </td>
                        <td>
                        <input size="50" type="text" name="everypay_public_key" value="<?php echo $everypay_public_key; ?>" placeholder="<?php echo $entry_public_key; ?>" id="input-public-key" class="form-control" />
                        <?php if ($error_public_key) { ?>
                            <div class="text-danger"><?php echo $error_public_key; ?></div>
                         <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="input-secret-key"><?php echo $entry_secret_key; ?>:</label></td>
                        <td>
                            <input size="50" type="text" name="everypay_secret_key" value="<?php echo $everypay_secret_key; ?>" placeholder="<?php echo $entry_secret_key; ?>" id="input-secret-key" class="form-control" />
                            <?php if ($error_secret_key) { ?>
                            <div class="text-danger"><?php echo $error_secret_key; ?></div>
                            <?php } ?>
                        </td>
                    </tr>

                    <tr>
                        <td><label for="input-sandbox"><?php echo $entry_sandbox; ?>:</label></td>
                        <td>
                            <input type="hidden" name="everypay_sandbox" id="_input-sandbox" value="0" />
                            <input type="checkbox" name="everypay_sandbox" id="input-sandbox" <?php echo $everypay_sandbox ? 'checked': null?> value="1" />
                        </td>
                    </tr>

                    <tr>
                        <td><label for="input-order-status"><?php echo $entry_order_status; ?>: <span class="help"><?php echo $help_order_status; ?></span></label></td>
                        <td>
                            <select name="everypay_order_status_id" id="input-order-status" class="form-control">
                            <?php foreach ($order_statuses as $order_status) { ?>
                            <?php if (($everypay_order_status_id and $order_status['order_status_id'] == $everypay_order_status_id) or $order_status['order_status_id'] == 2) { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><label for="input-status"><?php echo $entry_status; ?>:</label></td>
                        <td>
                            <select name="everypay_status" id="input-status" class="form-control">
                                <?php if ($everypay_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><label for="input-sort-order"><?php echo $entry_sort_order; ?>:</label></td>
                        <td>
                            <input size="1" type="text" name="everypay_sort_order" value="<?php if($everypay_sort_order){echo $everypay_sort_order;} else echo 0; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" />
                        </td>
                    </tr>

                    <tr>
                        <td><label style="font-size: 20px; font-weight: bold;"><?php echo $text_installments; ?></label></td>
                        <td>
                            <?php if ($error_installments) { ?>
                            <div class="text-danger"><?php echo $error_installments; ?></div>
                            <?php } ?>
                            <input id="everypay-installments" type="hidden" name="everypay_installments" value="<?php echo isset($everypay_installments) ? $everypay_installments : null ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" id="installments"></td>
                    </tr>

                </table>

            </form>
        </div>
    </div>
</div>
<script type="text/x-tmpl-mustache" id="installment-row">
<tr data-id="{{id}}" style="text-align: center">
    <td><input size="3" type="text" name="amount_{{id}}_from" value="{{from}}" class="form-control" /></td>
    <td><input size="3" type="text" name="amount_{{id}}_to" value="{{to}}" class="form-control" /></td>
    <td><input size="1" type="text" name="max_{{id}}" value="{{max}}" class="form-control" /></td>
    <td><a class="remove-installment" href="#" style="font-weight: bold; font-size:20px; text-decoration: none; color: red;">&#8722;</a></td>
</tr>
</script>
<script type="text/x-tmpl-mustache" id="installment-table">
<table class="form">
    <thead>
        <tr>
            <th><?php echo $text_installment_amount_from ?></th>
            <th><?php echo $text_installment_amount_to ?></th>
            <th><?php echo $text_installment_number ?></th>
            <th><a href="#" id="add-installment" style="font-weight: bold; font-size:20px; text-decoration: none; color: green;">&#43;</a></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
</script>
<?php echo $footer; ?>
