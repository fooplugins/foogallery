<div class="foopilot-purchase-points">
    <h2>Purchase Credits</h2>
    <p>You can purchase credits to unlock foopilot features.</p>
    <form id="purchase-form">
        <label for="credit_amount">Select the number of credits to purchase:</label>
        <select name="credit_amount" id="credit_amount">
            <option value="10">10 credits - $0.99</option>
            <option value="20">20 credits - $1.49</option>
        </select>
        <button type="submit" class="button button-primary foopilot-purchase-points">Purchase Credits</button>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
    $('.foopilot-purchase-points').on('click', function(event) {
        event.preventDefault();
        var selectedCredits = $('#credit_amount').val();        
    });
});
</script>