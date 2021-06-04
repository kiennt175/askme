@extends('layouts.master')

@section('style')
	@parent
    <link rel="stylesheet" href="{{ asset('css/editor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ipa.css') }}">
    <link rel="stylesheet" href="{{ asset('css/recorder.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/image-uploader/css/font.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/image-uploader/css/image-uploader.css') }}">
    <link rel="stylesheet" href="{{ asset('css/image-uploader.css') }}">
    <link rel="stylesheet" href="{{ asset('css/audioUploader.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ask-question.css') }}">
    <link rel="stylesheet" href="{{ asset('datetimepicker/style.min.css') }}">
    <script>
        if (!document.addEventListener) {
            parent.location.href = 'ie8/type.html';
        }
        //--------------------------------------------------------------------
        if (parent.location.href != window.location.href) {
            parent.location.href = window.location.href;
        }
        //--------------------------------------------------------------------
        var f = 0;
        var left = "pete";
        var right = "e-lang.co.uk";
        //--------------------------------------------------------------------
        var report = 'typefb.php';
        var about = 'typehelp.html';
        var home = 'http://mackichan.e-lang.co.uk/javascript-call-activities/';
        var again = 'type.html';
        var selectedString = "";
        var ipaCode = new Array();
        ipaCode[0] = new Array("", "105,720", "618", "650", "117,720", "618,601", "101,618", "712", "716");
        ipaCode[1] = new Array("", "101", "601", "604,720", "596,720", "650,601", "596,618", "601,650");
        ipaCode[2] = new Array("", "230", "652", "593,720", "594", "101,601", "097,618", "097,650");
        ipaCode[3] = new Array("", "112", "98", "116", "100", "679", "676", "107", "103");
        ipaCode[4] = new Array("", "102", "118", "952", "240", "115", "122", "643", "658");
        ipaCode[5] = new Array("", "109", "110", "331", "104", "108", "114", "119", "106");
        //I changed this to specify a zero array length to overcome problems in IE 5.5
        var myAnswer = new Array(0);
        var help = '';
        var webNav = new Array("webIE", "webNot");
        var wordNav = new Array("wordIE", "wordNot");
        //--------------------------------------------------------------------
        browser = navigator.appName;
        var IE = false;
        if (browser == "Microsoft Internet Explorer") {
            IE = true;
        }
        //--------------------------------------------------------------------
        function doAnswer() {
            var answerString = "";
            var oldString = "";
            var oldStringLong = "";
            var partOne = "";
            var partTwo = "";
            var finalAnswer = "";
            var finalAnswerWord = "";
            var finalAnswerWeb = "";
            for (i = 0; i < myAnswer.length; i++) {
                oldString = myAnswer[i];
                if (oldString.length > 3) {
                    partOne = oldString.substr(0, 3)
                    partTwo = oldString.substr(4, 7)
                    answerString = answerString + String.fromCharCode(partOne);
                    answerString = answerString + String.fromCharCode(partTwo);
                    oldStringLong = oldStringLong + '&#' + partOne + ';';
                    oldStringLong = oldStringLong + '&#' + partTwo + ';';
                    partOne = partOne.replace('058', '720')
                    partTwo = partTwo.replace('058', '720')
                    finalAnswerWord = finalAnswerWord + String.fromCharCode(partOne);
                    finalAnswerWord = finalAnswerWord + String.fromCharCode(partTwo);
                }
                else {
                    answerString = answerString + String.fromCharCode(myAnswer[i]);
                    oldStringLong = oldStringLong + '&#' + oldString + ';';
                    tempString = myAnswer[i];
                    tempString = tempString.replace('058', '720');
                    finalAnswerWord = finalAnswerWord + String.fromCharCode(tempString);
                }
            }
            if (answerString.length > 0) {
                finalAnswer = "/" + answerString + "/";
            }
            else {
                finalAnswer = answerString;
            }
            document.forms[2].resultview.value = finalAnswer;
            document.forms[2].result.value = '/' + finalAnswerWord + '/';
            finalAnswerWeb = oldStringLong;
            document.forms[2].resultweb.value = '/' + finalAnswerWeb + '/';
        }
        //--------------------------------------------------------------------
        function chooseMe(foo) {
            cx = foo % 10;
            rx = Math.floor(foo / 10);
            b = myAnswer.length;
            myAnswer[b] = ipaCode[rx][cx];
            doAnswer();
        }
        //--------------------------------------------------------------------
        function addSpace() {
            b = myAnswer.length;
            myAnswer[b] = "032";
            doAnswer();
        }
        //--------------------------------------------------------------------
        function clearOne() {
            if (myAnswer.length > 0) {
                myAnswer.length = myAnswer.length - 1;
            }
            doAnswer();
        }
        //--------------------------------------------------------------------
        function clearAll() {
            myAnswer.length = 0;
            //there is a problem here with some IE5.5 - not an object. Why?
            doAnswer();
        }
        //--------------------------------------------------------------------
        function buttonCaption(foo) {
            var keyString = "";
            if (foo.length > 3) {
                keyString = keyString + '&#' + foo.substr(0, 3) + ';' + '&#' + foo.substr(4, 7) + ';';
            }
            else {
                keyString = keyString + '&#' + foo + ';';
            }
            return (keyString);
        }
    </script>
@endsection

@section('content')
    <div class="breadcrumbs">
        <section class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Ask Question</h1>
                </div>
            </div>
        </section>
    </div>
    {{-- <section class="container main-content">
        <div class="row"> --}}
            <div class="col-md-8">
                <div class="page-content ask-question">
                    <div class="boxedtitle page-title"><h2>Ask A Public Question</h2></div>
                    <div class="form-style form-style-3" id="question-submit">
                        <form action="{{ route('user.postQuestion') }}" method="post" id="post-question" enctype="multipart/form-data">
                            @csrf
							<div class="form-inputs clearfix">
                                <div class="schedule-block">
                                    <label class="schedule-label">Schedule</label>
                                    <input name="datetime" type="text" id="datetime-picker" placeholder="You can select datetime here to post this question...">
                                </div>
								<p>
									<label class="required">Question Title<span>*</span></label>
									<input type="text" id="question-title" name="title" required>
									<span class="form-description">Please choose an appropriate title for the question to answer it even easier.</span>
								</p>
								<p>
									<label class="required">Tags<span>*</span></label>
									<input type="text" class="input" name="tags" id="question_tags" data-seperator=",">
									<span class="form-description">Please choose  suitable Keywords Ex : <span class="color">question , poll</span> .</span>
								</p>
							</div>
							<div id="form-textarea">
								<p>
									<label class="required">Body<span>*</span></label>
                                    <div id="container">
                                        <div id="editor"></div>
                                        {{-- <div id="sidebar"></div> --}}
                                    </div>
								</p>
							</div>
                            <br><br>
                            <div class="form-inputs clearfix">
                                <p>
                                    <label>Images</label>
                                    {{-- <input type="file" name="images[]" accept="image/x-png,image/gif,image/jpeg,image/jpg" multiple> --}}
                                    <div class="input-field">
                                        <div class="input-images-2" style="padding-top: .5rem;"></div>
                                    </div>
                                </p>
                                <br>
                                <p>
                                    <label>Audio Files</label>
                                    {{-- <input type="file" name="medias[]" accept="audio/mp3,audio/ogg,audio/wav" multiple> --}}
                                    <div class="add-more-media">
                                        <div class="add-more-media-form">
                                            <div class="file-uploader__message-area">
                                                <p>Select a file to upload</p>
                                            </div>
                                            <div class="file-chooser"> 
                                                <input class="file-chooser__input" type="file" accept="audio/mp3,audio/ogg,audio/wav">
                                            </div>
                                        </div>
                                    </div>
                                </p>
                            </div>
							<p class="form-submit">
								<input type="submit" id="publish-question" value="Publish Your Question" class="button color small submit">
							</p>
						</form>
                    </div>
                </div>
            </div>
            <aside class="col-md-4 sidebar" style="position: sticky; top: 0; margin-bottom: 86px;">
                <div class="widget">
                    <h3 class="widget_title">IPA Tool</h3>
                    <form method="post" enctype="application/x-www-form-urlencoded" action="#">
                        <input type="text" name="resultview" class="resvew" style="font-size: 16px">
                        <div id="formcontent">
                            <input type="text" id="result" class="res"/>
                            <input type="text" id="resultweb" class="res"/>
                        </div>
                        <div class="typewriter">
                            <script type="text/javascript">
                                for(j = 0, z = 1; j < 1; j++, z = z + 10) {
                                    for (i = 1, k = z; i < 5; i++, k++) {
                                        p = k % 10;
                                        document.write('<input type=\"button\" class=\"vowel\" onclick=\"javascript:chooseMe(' + k + ');\" value=\"' + buttonCaption(ipaCode[j][p]) + '\"/> ');
                                    }
                                    for (i = 5; i < 7; i++, k++) {
                                        p = k % 10;
                                        document.write('<input type=\"button\" class=\"dipth\" onclick=\"javascript:chooseMe(' + k + ');\" value=\"' + buttonCaption(ipaCode[j][p]) + '\"/> ');
                                    }
                                    for (i = 7; i < 9; i++, k++) {
                                        p = k % 10;
                                        document.write('<input type=\"button\" class=\"stress\" onclick=\"javascript:chooseMe(' + k + ');\" value=\"' + buttonCaption(ipaCode[j][p]) + '\"/> ');
                                    }
                                    document.write('<br/>');
                                }
                                for (j = 1, z = 11; j < 3; j++, z = z + 10) {
                                    for (i = 1, k = z; i < 5; i++, k++) {
                                        p = k % 10;
                                        document.write('<input type=\"button\" class=\"vowel\" onclick=\"javascript:chooseMe(' + k + ');\" value=\"' + buttonCaption(ipaCode[j][p]) + '\"/> ');
                                    }
                                    for (i = 5; i < 8; i++, k++) {
                                        p = k % 10;
                                        document.write('<input type=\"button\" class=\"dipth\" onclick=\"javascript:chooseMe(' + k + ');\" value=\"' + buttonCaption(ipaCode[j][p]) + '\"/> ');
                                    }
                                    document.write('<br/>');
                                }
                                for (j = 3, z = 31; j < 6; j++, z = z + 10) {
                                    for (i = 1, k = z; i < 9; i++, k++) {
                                        p = k % 10;
                                        document.write('<input type=\"button\" class=\"vowel\" onclick=\"javascript:chooseMe(' + k + ');\" value=\"' + buttonCaption(ipaCode[j][p]) + '\"/> ');
                                    }
                                    document.write('<br/>');
                                }
                            </script>
                            <br/>
                            <input type="button" onclick="clearOne();" class="buact" value="Delete"/>
                            <input type="button" onclick="addSpace();" class="buact" value="Space"/>
                            <input type="button" onclick="clearAll();;" class="buact" value="Reset"/>
                            <br/><br/>
                        </div>
                    </form>
                </div>
                <div class="widget">
                    <h3 class="widget_title">Voice Recorder Tool</h3>
                    <div id='gUMArea'>
                        <button class="btn btn-default" id='gUMbtn'>Request Voice Recorder</button>
                    </div>
                    <div id="record">
                        <div id='btns'>
                            <button class="btn btn-default" id='start'>Start</button>
                            <button class="btn btn-default" id='stop'>Stop</button>
                        </div>
                        <div id="img-block">
                            <img id="gif" src="{{ asset('images/recording.gif') }}" alt="">
                        </div>
                    </div>
                    <div id="save">
                        <ul class="list-unstyled" id='ul'></ul>
                    </div>
                </div>
                <div class="widget widget_tag_cloud">
                    <h3 class="widget_title">Hottest Tags</h3>
                    <a href="#">projects</a>
                    <a href="#">Portfolio</a>
                    <a href="#">Wordpress</a>
                    <a href="#">Html</a>
                    <a href="#">Css</a>
                    <a href="#">jQuery</a>
                    <a href="#">2code</a>
                    <a href="#">vbegy</a>
                </div>
            </aside>
        {{-- </div>
    </section> --}}
    <script src="{{ asset('js/editor.js') }}"></script>
    <script>
        $('.input-images-2').imageUploader({
            preloaded: [],
            imagesInputName: 'photos',
            preloadedInputName: 'oldImageIds'
        });
    </script>
    <script>
        $("#datetime-picker").flatpickr({
            enableTime: true,
            minDate: 'today',
            maxDate: new Date().fp_incr(7) 
        });
    </script>
@endsection

@section('scripts')
	@parent
    <script>
        var avatarURL = "{{ $avatar }}";
        avatarURL = avatarURL.replace('amp;', '');
    </script>
    <script src="{{ asset('js/recorder.js') }}"></script>
    <script src="{{ asset('js/postQuestion.js') }}"></script>
    <script src="{{ asset('js/askQuestionPage.js') }}"></script>
    <script src="{{ asset('js/audioUploader.js') }}"></script>
    <script src="{{ asset('bower_components/image-uploader/js/image-uploader.js') }}"></script>
    <script src="{{ asset('datetimepicker/script.min.js') }}"></script>
@endsection
