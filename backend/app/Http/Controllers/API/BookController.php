<?php

namespace App\Http\Controllers\API;

use App\Book;
use App\Models\News;
use App\Http\Controllers\Controller;
use Debugbar;
use Illuminate\Http\Request;
use SimpleXmlElement;
use HTMLDomParser;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $book;
    private $news;

    public function __construct(Book $book,
                                News $news)
    {
        $this->book = $book;
        $this->news = $news;
    }

    public function autoCopy() {
        $all_book = $this->book->getAll();
        foreach($all_book as $book) {
            $data = $this->copy($book->rss);
            foreach ($data as $item) {
                    $this->news->create(
                    [
                        'title'  => $item['title'],
                        'url'    => $item['title'],
                        'content'=> $item['data'],
                    ]
                );
            }
        }

        echo 'success';
    }

    private function copy($rss_link) {
        $post_data = [];
        $data = file_get_contents($rss_link);
        $x = new SimpleXmlElement($data);
        foreach($x->channel->item as $entry) {
            $name = HTMLDomParser::file_get_html($entry->link);
            if(isset($name->find('div.exp_content')[0])) {
                $title = $name->find('h1 a')[0];
                $title = $title->plaintext;
                $test  = $name->find('div.exp_content')[0];
                if(isset($test->find('#bongda_player')[0])) {
                    $test2 = $test->find('#bongda_player')[0];
                    $test3 = $test2->find('#youtube_iframe')[0];                                      
                    $link  = $test3->attr['data-autoplay-src'];
                    $youtube_embbed = '<iframe width="300" height="400" src="'.$link.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
                    $test = str_replace($test2, $youtube_embbed, $test);  
                }
                $test = str_replace('http://www.bongda.com.vn', 'http://cuongbongdatv.com', $test);
                $data = ['title' => $title,
                         'data'  => $test,
                         'time'  => time() 
                    ]; 

                $post_data[] = $data;
            }
        }

        return $post_data;
    }
    public function index()
    {
        try {
            $book = Book::paginate(3);
            return response()->success($book);
        } catch (Exception $e) {
            Debugbar::addThrowable($e);
            return response()->exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'rss'      => 'required',
                'time' => 'required',
            ]);
            $book = Book::create($request->all());
            if ($book) {
                return response()->success($book, 200);
            } else {
                return response()->error($book, 400);
            }
        } catch (Exception $e) {
            Debugbar::addThrowable($e);
            return response()->exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $book = Book::find($id);
            return response()->success($book);
        } catch (Exception $e) {
            Debugbar::addThrowable($e);
            return response()->exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            /*$book              = new Book;
            $book->author      = $request->input('author');
            $book->description = $request->input('description');
            $book->save();*/
            $book = Book::findorFail($id);
            $book = $book->update($request->all());
            return response()->success($book);
        } catch (Exception $e) {
            Debugbar::addThrowable($e);
            return response()->exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $book = Book::find($id);
            $res  = $book->delete();
            return response()->success($res);
        } catch (Exception $e) {
            Debugbar::addThrowable($e);
            return response()->exception($e->getMessage(), $e->getCode());
        }
    }
}
