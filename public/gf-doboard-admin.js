jQuery(document).ready(function($){
    let $select = $('select[name="_gform_setting_doBoard_label_ids"]');
    if ($select.length && $select.prop('multiple')) {
        let name = $select.attr('name');
        if (name && name.indexOf('[]') === -1) {
            $select.attr('name', name + '[]');
        }
    }

    // Account → Projects
    $(document).on('change', 'select[name="_gform_setting_doBoard_account_id"]', function(){
        let account_id = $(this).val();
        let session_id = $('input[name="_gform_setting_doBoard_session_id"]').val();
        let nonce = $('input[name="gform_settings_save_nonce"]').val();

        // Reset project and task board selects
        $('select[name="_gform_setting_doBoard_project_id"]').html('<option>Loading...</option>').prop('disabled', true);
        $('select[name="_gform_setting_doBoard_task_board_id"]').html('<option>Select project first</option>').prop('disabled', true);
        $('select[name="_gform_setting_doBoard_label_ids[]"]').html('<option>Select account first</option>').prop('disabled', true);

        // AJAX-request to get projects
        $.post(ajaxurl, {
            action: 'gf_doboard_get_projects',
            account_id: account_id,
            session_id: session_id,
            gform_settings_save_nonce: nonce
        }, function(response){
            let html = '';
            $.each(response.data, function(i, project){
                html += '<option value="'+project.value+'">'+project.label+'</option>';
            });
            let $project = $('select[name="_gform_setting_doBoard_project_id"]');
            $project.html(html).prop('disabled', false);
        });
    });

    // Project → Task Boards
    $(document).on('change', 'select[name="_gform_setting_doBoard_project_id"]', function(){
        let account_id = $('select[name="_gform_setting_doBoard_account_id"]').val();
        let session_id = $('input[name="_gform_setting_doBoard_session_id"]').val();
        let project_id = $(this).val();
        let nonce = $('input[name="gform_settings_save_nonce"]').val();

        $('select[name="_gform_setting_doBoard_task_board_id"]').html('<option>Loading...</option>').prop('disabled', true);

        $.post(ajaxurl, {
            action: 'gf_doboard_get_task_boards',
            account_id: account_id,
            session_id: session_id,
            project_id: project_id,
            gform_settings_save_nonce: nonce
        }, function(response){
            let html = '';
            $.each(response.data, function(i, board){
                html += '<option value="'+board.value+'">'+board.label+'</option>';
            });
            let $taskBoard = $('select[name="_gform_setting_doBoard_task_board_id"]');
            $taskBoard.html(html).prop('disabled', false);
        });
    });

    // Account → Labels (labels are independent of project)
    $(document).on('change', 'select[name="_gform_setting_doBoard_account_id"]', function(){
        let account_id = $(this).val();
        let session_id = $('input[name="_gform_setting_doBoard_session_id"]').val();
        let nonce = $('input[name="gform_settings_save_nonce"]').val();

        $('select[name="_gform_setting_doBoard_label_ids[]"]').html('<option>Loading...</option>').prop('disabled', true);

        $.post(ajaxurl, {
            action: 'gf_doboard_get_labels',
            account_id: account_id,
            session_id: session_id,
            gform_settings_save_nonce: nonce
        }, function(response){
            let html = '';
            $.each(response.data, function(i, label){
                html += '<option value="'+label.value+'">'+label.label+'</option>';
            });
            let $labels = $('select[name="_gform_setting_doBoard_label_ids[]"]');
            $labels.html(html).prop('disabled', false);
        });
    });
});