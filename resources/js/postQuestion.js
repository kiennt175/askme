$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var form = $('#post-question');
    form.on('submit', function(e){
        e.preventDefault();
        imgs = document.querySelectorAll(".uploaded-image");
        var imgUrls = [];
        imgs.forEach(img => {
            imgUrls.push(img.getAttribute('data-alt'));
        });
        var formData = new FormData($("#post-question")[0]);
        console.log(theEditor.getData())
        formData.append('content', theEditor.getData());
        formData.append('imgUrls', imgUrls);
        $.ajax({
            type: "POST",
            url: form.attr('action'),
            data: formData,
            processData: false, // for multipart/form-data
            contentType: false, // for multipart/form-data
            success: function(data){
                if (data.response == 1) {
                    window.location.href = "http://localhost:8000/user/newsfeed";
                } else {
                    tata.error('Ask Question', 'Please fill out the required fields!', {
                        duration: 5000,
                        animate: 'slide'
                    });
                }
            },
            error: function(error){
                tata.error('Ask Question', 'Failed to post the question!', {
                    duration: 5000,
                    animate: 'slide'
                });
            }
        });
    });
});
