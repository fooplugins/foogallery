jQuery(document).ready(function($) {
    // Handle click event for Foopilot buttons
    $(document).on('click', '.foogallery-foopilot', handleFoopilotButtonClick);

    // Handle click event for Cancel button
    $( '.foogallery-foopilot-modal-cancel, .media-modal-close' ).on('click', function (event) {
        event.preventDefault();
        closeFoopilotModal();
    });

    // Handle click event for foopilot signup buttons
    $( '.foogallery-foopilot-signup-form-inner-content-button' ).on('click', function (event) {
        event.preventDefault();
        var email = $( '#foopilot-email' ).val();
        var nonce = '<?php echo wp_create_nonce("foopilot_nonce"); ?>';
        // Make Ajax call to generate API key
        generateFoopilotAPIKey(email, nonce);
    });

    // Function to handle Foopilot button click
    function handleFoopilotButtonClick(event) {
        event.preventDefault();
        var task = $(this).data('task');
        var ajaxData = {
            action: 'foopilot_generate_task_content',
            task: task,
            foopilot_nonce: '<?php echo wp_create_nonce("foopilot_nonce"); ?>'
        };
        // Send AJAX request to generate task content dynamically
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                handleTaskResponse(task, response);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); // Log any errors
            }
        });
    }

    // Function to handle task response
    function handleTaskResponse(task, response) {
        $('.foopilot-task-html').html(response);
        $('#foopilot-modal').show();
        if (task !== 'credit') {
            handlePointDeduction();
        }
    }

    // Function to handle point deduction
    function handlePointDeduction() {
        var currentPoints = parseInt($('#foogallery-credit-points').text());
        var pointsToDeduct = 1; // will be determined by FOOPILOT API
        if (currentPoints >= pointsToDeduct) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'deduct_foopilot_points',
                    points: pointsToDeduct,
                    foopilot_nonce: '<?php echo wp_create_nonce("foopilot_nonce"); ?>'
                },
                success: function(response) {
                    $('#foogallery-credit-points').html(response.data);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText); // Log any errors
                }
            });
        } else {
            $('.foopilot-task-html').html('Insufficient points to perform this task.');
        }
    }

    // Function to generate Foopilot API key
    function generateFoopilotAPIKey(email, nonce) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_foopilot_api_key',
                email: email,
                foopilot_nonce: nonce
            },
            success: function () {
                // Reload the modal content dynamically
                $("#fg-foopilot-modal").load(" #fg-foopilot-modal");
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText); // Log errors
            }
        });
    }

    // Function to close the modal
    function closeFoopilotModal() {
        $( '#fg-foopilot-modal' ).hide();
    }

    // Handle click event for purchasing points
    $('.foopilot-purchase-points').on('click', function(event) {
        event.preventDefault();
        var selectedCredits = $('#credit_amount').val();
        // Add logic for purchasing points
    });
});
