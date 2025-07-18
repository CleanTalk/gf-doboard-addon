jQuery(document).ready(function($){
    var $select = $('select[name="_gform_setting_doBoard_label_ids"]');
    if ($select.length && $select.prop('multiple')) {
        var name = $select.attr('name');
        if (name && name.indexOf('[]') === -1) {
            $select.attr('name', name + '[]');
        }
    }

    // Account → Projects
    $(document).on('change', 'select[name="_gform_setting_doBoard_account_id"]', function(){
        var account_id = $(this).val();
        var session_id = $('input[name="_gform_setting_doBoard_session_id"]').val();

        // Reset project and task board selects
        $('select[name="_gform_setting_doBoard_project_id"]').html('<option>Loading...</option>').prop('disabled', true);
        $('select[name="_gform_setting_doBoard_task_board_id"]').html('<option>Select project first</option>').prop('disabled', true);
        $('select[name="_gform_setting_doBoard_label_ids[]"]').html('<option>Select account first</option>').prop('disabled', true);

        // AJAX-request to get projects
        $.post(ajaxurl, {
            action: 'gf_doboard_get_projects',
            account_id: account_id,
            session_id: session_id
        }, function(response){
            var html = '';
            $.each(response.data, function(i, project){
                html += '<option value="'+project.value+'">'+project.label+'</option>';
            });
            var $project = $('select[name="_gform_setting_doBoard_project_id"]');
            $project.html(html).prop('disabled', false);
        });
    });

    // Project → Task Boards
    $(document).on('change', 'select[name="_gform_setting_doBoard_project_id"]', function(){
        var account_id = $('select[name="_gform_setting_doBoard_account_id"]').val();
        var session_id = $('input[name="_gform_setting_doBoard_session_id"]').val();
        var project_id = $(this).val();

        $('select[name="_gform_setting_doBoard_task_board_id"]').html('<option>Loading...</option>').prop('disabled', true);

        $.post(ajaxurl, {
            action: 'gf_doboard_get_task_boards',
            account_id: account_id,
            session_id: session_id,
            project_id: project_id
        }, function(response){
            var html = '';
            $.each(response.data, function(i, board){
                html += '<option value="'+board.value+'">'+board.label+'</option>';
            });
            var $taskBoard = $('select[name="_gform_setting_doBoard_task_board_id"]');
            $taskBoard.html(html).prop('disabled', false);
        });
    });

    // Account → Labels (labels are independent of project)
    $(document).on('change', 'select[name="_gform_setting_doBoard_account_id"]', function(){
        var account_id = $(this).val();
        var session_id = $('input[name="_gform_setting_doBoard_session_id"]').val();

        $('select[name="_gform_setting_doBoard_label_ids[]"]').html('<option>Loading...</option>').prop('disabled', true);

        $.post(ajaxurl, {
            action: 'gf_doboard_get_labels',
            account_id: account_id,
            session_id: session_id
        }, function(response){
            var html = '';
            $.each(response.data, function(i, label){
                html += '<option value="'+label.value+'">'+label.label+'</option>';
            });
            var $labels = $('select[name="_gform_setting_doBoard_label_ids[]"]');
            $labels.html(html).prop('disabled', false);
        });
    });
});