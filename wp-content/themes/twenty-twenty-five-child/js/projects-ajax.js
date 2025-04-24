jQuery(function($){
    $('#fetch-projects-btn').on('click', function(){
      $.ajax({
        url: projectsAjax.ajax_url,
        method: 'POST',
        dataType: 'json',
        data: {
          action: 'fetch_projects',
          _ajax_nonce: projectsAjax.nonce
        },
        success(response) {
          if (response.success) {
            var html = '<ul>';
            response.data.forEach(function(item){
              html += '<li>'
                   + '<a href="' + item.link + '" target="_blank">'
                   + item.title
                   + '</a> (ID: ' + item.id + ')'
                   + '</li>';
            });
            html += '</ul>';
            $('#projects-result').html(html);
          } else {
            $('#projects-result').text('No projects found.');
          }
        },
        error(jqXHR, textStatus, errorThrown) {
          console.error('AJAX error:', textStatus, errorThrown);
          $('#projects-result').text('AJAX request failed.');
        }
      });
    });
  });
  