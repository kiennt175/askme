<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Media;
use App\Models\Tag;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Arr;

class QuestionController extends Controller
{
    public function show($id)
    {
        $question = Question::with(['images', 'medias', 'votes', 'content', 'user', 'tags', 'answers.comments.user', 'answers.user', 'answers.votes', 'answers.content', 'answers.conversation', 'answers.comments.user'])->where('id', $id)->first();
        $answers = Answer::with(['images', 'medias', 'user', 'comments.user'])->where('question_id', $id)->paginate(5);
        $votedCheck = $question->votes->where('user_id', Auth::id())->first();
        $answerUserIds = [];
        $answerUserNames = [];
        $answerUserAvatars = [];
        $answerContents = [];
        $answerConversations = [];
        $answerVotedCheck = [];
        $answerIds = [];
        foreach ($question->answers as $key => $answer) {
            array_push($answerUserIds, $answer->user->id);
            array_push($answerUserNames, $answer->user->name);
            array_push($answerUserAvatars, $answer->user->avatar);
            array_push($answerContents, $answer->content->content);
            array_push($answerConversations, $answer->conversation->conversation ?? '[]');
            array_push($answerIds, $answer->id);
            if (!$answer->votes->where('user_id', Auth::id())->first()) {
                array_push($answerVotedCheck, 0);
            } else {
                array_push($answerVotedCheck, 1);
            }
        }
        $sortBy = 'oldest';

        return view('question_details', compact(['question', 'answers', 'votedCheck', 'answerUserIds', 'answerUserNames', 'answerUserAvatars', 'answerContents', 'answerConversations', 'answerVotedCheck', 'answerIds', 'sortBy']));
    }

    public function showBy($id, $sortBy)
    {
        $question = Question::with(['images', 'medias', 'votes', 'content', 'user', 'tags', 'answers.comments.user', 'answers.user', 'answers.votes', 'answers.content', 'answers.conversation', 'answers.comments.user'])->where('id', $id)->first();
        $allAnswers = Answer::with(['images', 'medias', 'user', 'content', 'conversation', 'votes', 'comments.user'])->where('question_id', $id)->orderBy($sortBy, 'desc')->orderBy('id', 'asc')->get();
        $answers = Answer::with(['images', 'medias', 'user'])->where('question_id', $id)->orderBy($sortBy, 'desc')->orderBy('id', 'asc')->paginate(5);
        $votedCheck = $question->votes->where('user_id', Auth::id())->first();
        $answerUserIds = [];
        $answerUserNames = [];
        $answerUserAvatars = [];
        $answerContents = [];
        $answerConversations = [];
        $answerVotedCheck = [];
        $answerIds = [];
        foreach ($allAnswers as $answer) {
            array_push($answerUserIds, $answer->user->id);
            array_push($answerUserNames, $answer->user->name);
            array_push($answerUserAvatars, $answer->user->avatar);
            array_push($answerContents, $answer->content->content);
            array_push($answerConversations, $answer->conversation->conversation ?? '[]');
            array_push($answerIds, $answer->id);
            if (!$answer->votes->where('user_id', Auth::id())->first()) {
                array_push($answerVotedCheck, 0);
            } else {
                array_push($answerVotedCheck, 1);
            }
        }

        return view('question_details', compact(['question', 'answers', 'votedCheck', 'answerUserIds', 'answerUserNames', 'answerUserAvatars', 'answerContents', 'answerConversations', 'answerVotedCheck', 'answerIds', 'sortBy']));
    }

    public function vote($id)
    {
        $question = Question::with('votes')->where('id', $id)->first();
        $userId = Auth::id();
        $votedCheck = $question->votes->where('user_id', $userId)->first();
        if (!$votedCheck) {
            DB::transaction(function () use ($question, $userId) {
                $question->update([
                    'vote_number' => ++$question->vote_number
                ]);
                $question->votes()->create([
                    'user_id' => $userId
                ]);
                $question->user()->update([
                    'points' => ++$question->user->points
                ]);
            });

            return response()->json(['response' => 1]);
        } else {
            DB::transaction(function () use ($question, $userId) {
                $question->update([
                    'vote_number' => --$question->vote_number
                ]);
                $question->votes()->where('user_id', $userId)->delete();
                $question->user()->update([
                    'points' => --$question->user->points
                ]);
            });
            
            return response()->json(['response' => 0]);
        }
    }

    public function bestAnswer(Request $request, $questionId)
    {
        $question = Question::where('id', $questionId)->update([
            'best_answer_id' => $request->answerId
        ]);

        return response()->json(['response' => 1, 'answerId' => $request->answerId]);
    }

    public function destroy($questionId)
    {
        // lay cau hoi
        $question = Question::with(['tags', 'medias', 'images', 'votes', 'answers.medias', 'answers.images', 'answers.votes', 'answers.content', 'answers.conversation', 'answers.comments'])->where('id', $questionId)->first();

        DB::transaction(function () use ($question, $questionId) {
            // lay cac tagIds can xoa
            $tagIds = array_column($question->tags->toArray(), 'id');
            $deleteTags = [];
            $questionCountForEachTag = DB::table('question_tag')
                ->select(DB::raw('count(*) as question_count, tag_id'))
                ->whereIn('tag_id', $tagIds)
                ->groupBy('tag_id')
                ->get();
            foreach ($questionCountForEachTag as $key => $questionCount) {
                if ($questionCount->question_count == 1) 
                    array_push($deleteTags, $questionCount->tag_id);
            }
            // xoa cac tags
            DB::table('question_tag')->where('question_id', $questionId)->delete();
            Tag::whereIn('id', $deleteTags)->delete();
            
            // xoa medias cua question
            $mediaPaths = [];
            foreach ($question->medias as $key => $media) {
                $mediaPath = str_replace('http://localhost:8000/medias/', '', $media->url);
                $mediaPath = public_path('medias') . '/' . $mediaPath;   
                array_push($mediaPaths, $mediaPath);
            }
            File::delete($mediaPaths);
            $question->medias()->delete();

            // xoa images cua question
            $imagePaths = [];
            foreach ($question->images as $key => $image) {
                $imagePath = str_replace('http://localhost:8000/images/uploads', '', $image->url);
                $imagePath = public_path('images/uploads') . '/' . $imagePath;   
                array_push($imagePaths, $imagePath);
            }
            File::delete($imagePaths);
            $question->images()->delete();

            // xoa votes
            $question->votes()->delete();

            // xoa answers
            $answerMediaPaths = [];
            $answerImagePaths = [];
            foreach ($question->answers as $key1 => $answer) {
                // xoa medias cua answers
                foreach ($answer->medias as $key2 => $media) {
                    $answerMediaPath = str_replace('http://localhost:8000/medias/', '', $media->url);
                    $answerMediaPath = public_path('medias') . '/' . $answerMediaPath;   
                    array_push($answerMediaPaths, $answerMediaPath);
                }
                $answer->medias()->delete();

                // xoa images cua answers
                $imagePaths = [];
                foreach ($answer->images as $key3 => $image) {
                    $answerImagePath = str_replace('http://localhost:8000/images/uploads', '', $image->url);
                    $answerImagePath = public_path('images/uploads') . '/' . $answerImagePath;   
                    array_push($answerImagePaths, $answerImagePath);
                }
                $answer->images()->delete();

                // xoa votes cua answers
                $answer->votes()->delete();

                // xoa contents cua answers
                $answer->content()->delete();

                // xoa comments cua answers
                $answer->comments()->delete();

                // xoa conversations cua answers
                $answer->conversation()->delete();
            }
            File::delete($answerMediaPaths);
            File::delete($answerImagePaths);
            
            // xoa content cua question
            $question->content()->delete();

            // xoa question
            $question->delete();
        });

        return response()->json(['response' => 1]);
    }

    public function edit($questionId)
    {
        $question = Question::with(['tags', 'content', 'medias', 'images'])->where('id', $questionId)->first();
        $tags = implode(",", $question->tags->pluck('tag')->toArray());
        $images = [];
        foreach ($question->images as $image) {
            array_push($images, ['id' => $image->id, 'src' => $image->url]);
        }
        // dd($question->medias->pluck('url', 'id'));
        $medias = $question->medias;

        return view('edit_question', compact(['question', 'tags', 'images', 'medias']));
    }
    public function update(Request $request, $questionId) 
    {   
        $this->validate($request, 
            [
                'title' => ['required', 'string', 'max:255'],
                'tags' => ['required'],
                'photos.*' => 'image|mimes:jpg,jpeg,png,gif|max:2048',
                'audios.*' => 'mimetypes:audio/mpeg,video/webm|max:3072'
            ]
        ); 
        if (!$request->content) {
            return response()->json(['response' => 0]); 
        } 
        $question = Question::with(['tags', 'content', 'medias', 'images'])->where('id', $questionId)->first();
        DB::transaction(function () use ($request, $question) {
            // save question: title, updated
            $question->update([
                'title' => $request->title,
                'updated' => 1
            ]);

            // save tags
            // them cac tags moi vao bang tags
            $tags = explode(",", $request->tags);
            $formattedTags = [];
            foreach ($tags as $tag) {
                $element = [
                    'tag' => $tag
                ];
                array_push($formattedTags, $element);
            }
            DB::table('tags')->insertOrIgnore($formattedTags);
            // lay ids cua tags cua question
            $tagIds = Arr::flatten(Tag::select('id')->whereIn('tag', $tags)->get()->toArray());
            // sync voi bang trung gian
            $question->tags()->sync($tagIds);
            // xoa tags ko co question

            // save content
            $question->content()->update([
                'content' => $request->content,
            ]);

            // save images
            // tat ca old images cua question
            $allImageIds = $question->images()->pluck('id')->toArray();

            // (neu nhu question ko co anh, hoac xoa het anh cu) va ko them anh moi
            if (!$request->oldImageIds && $question->images->count() > 0) {
                $imagePaths = [];
                foreach ($question->images as $key => $image) {
                    $imagePath = str_replace('http://localhost:8000/images/uploads', '', $image->url);
                    $imagePath = public_path('images/uploads') . '/' . $imagePath;   
                    array_push($imagePaths, $imagePath);
                }
                File::delete($imagePaths);
                $question->images()->delete();
            }

            // neu nhu ton tai cac anh cu, va co anh cu nao do bi xoa
            if ($request->oldImageIds && $request->oldImageIds != $allImageIds) {
                // lay images cần xoá
                $oldImageIds = [];
                foreach ($request->oldImageIds as $oldImageId) {
                    if ($oldImageId == 0) {
                        break;
                    } else {
                        array_push($oldImageIds, $oldImageId);
                    }
                }
                $deletedImageIds = array_diff($allImageIds, $oldImageIds);

                // xoa images
                $deletedImagePaths = [];
                $deletedImages = Image::whereIn('id', $deletedImageIds)->get();
                foreach ($deletedImages as $deletedImage) {
                    $deletedImagePath = str_replace('http://localhost:8000/images/uploads', '', $deletedImage->url);
                    $deletedImagePath = public_path('images/uploads') . '/' . $deletedImagePath;   
                    array_push($deletedImagePaths, $deletedImagePath);
                }
                File::delete($deletedImagePaths);
                Image::destroy($deletedImageIds);
            }

            // add new images

            if ($request->photos) {
                foreach ($request->photos as $image) {
                    if (in_array($image->getClientOriginalName(), array_filter(explode(",", $request->imgUrls)))) {
                        $imageName = time() . '_' . $image->getClientOriginalName();
                        $whereToSaveImage = public_path('images/uploads');
                        $image->move($whereToSaveImage, $imageName);
                        $url = "http://localhost:8000/images/uploads/$imageName" ;
                        $question->images()->create([
                            'url' => $url
                        ]);
                    }
                }
            } 

            $oldAudioIds = $question->medias()->pluck('id')->toArray();
            if ($oldAudioIds) {
                // update old audios
                // lay ids can xoa
                if (!$request->oldAudioIds) $deletedAudioIds = $oldAudioIds;
                else $deletedAudioIds = array_diff($oldAudioIds, $request->oldAudioIds);

                // xoa audios
                $deletedAudioPaths = [];
                $deletedAudios = Media::whereIn('id', $deletedAudioIds)->get();
                foreach ($deletedAudios as $deletedAudio) {
                    $deletedAudioPath = str_replace('http://localhost:8000/images/uploads', '', $deletedAudio->url);
                    $deletedAudioPath = public_path('images/uploads') . '/' . $deletedAudioPath;   
                    array_push($deletedAudioPaths, $deletedAudioPath);
                }
                File::delete($deletedAudioPaths);
                Media::destroy($deletedAudioIds);
            }

            // add new audios
            if ($request->audios) {
                foreach ($request->audios as $media) {
                    $mediaName = time() . '_' . $media->getClientOriginalName();
                    $whereToSaveMedia = public_path('medias');
                    $media->move($whereToSaveMedia, $mediaName);
                    $url = "http://localhost:8000/medias/$mediaName" ;
                    $question->medias()->create([
                        'url' => $url
                    ]);
                }
            } 
        });

        return response()->json(['response' => 1, 'questionId' => $questionId]);
    }

    public function view()
    {
        $questions = Question::with(['content', 'user', 'answers'])->orderByDesc('id')->get();
        
        return view('questions', compact('questions'));
    }

    public function viewByTab($searchText, $tab)
    {


        if ($searchText == 'noSearching') {
            if ($tab == 'unanswered') {
                $questions = Question::with(['content', 'user', 'answers'])->where('best_answer_id', null)->orderByDesc('id')->get();
            } 
            if ($tab == 'votes') {
                $questions = Question::with(['content', 'user', 'answers'])->orderByDesc('vote_number')->get();
            }
            if ($tab == 'newest') {
                $questions = Question::with(['content', 'user', 'answers'])->orderByDesc('id')->get();
            }

            return view('questions', compact('questions', 'tab'));
        } else {
            

            if ($tab == 'unanswered') {
                $questions = Question::with(['content', 'user', 'answers'])->where('title', 'like', '%' . $searchText . '%')->where('best_answer_id', null)->orderByDesc('id')->get();
            } 
            if ($tab == 'votes') {
                $questions = Question::with(['content', 'user', 'answers'])->where('title', 'like', '%' . $searchText . '%')->orderByDesc('vote_number')->get();
            }
            if ($tab == 'newest') {
                $results = Question::complexSearch([
                    'body' => [
                        'query' => [
                            'multi_match' => [
                                'query' => $searchText,
                                'fields' => ['title'],
                                'fuzziness' => 'AUTO'
                            ]
                        ]
                    ]
                ])->getHits()['hits'];
    
                $ids = array_map(function($item){
                    return (int) $item['_id'];
                }, $results);
    
                $questions = Question::whereIn('id', $ids)->get();
                //$questions = Question::with(['content', 'user', 'answers'])->where('title', 'like', '%' . $searchText . '%')->orderByDesc('id')->get();
            }

            return view('questions', compact('questions', 'tab', 'searchText'));
        }
    }

    // public function search($searchText)
    // {
    //     $questions = Question::with(['content', 'user', 'answers'])->where('title', 'like', '%' . $searchText . '%')->orderByDesc('id')->get();

    //     return response()->json(['response' => 1, 'questions' => $questions, 'searchText' => $searText]);
    // }
}