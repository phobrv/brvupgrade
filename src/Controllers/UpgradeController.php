<?php

namespace Phobrv\BrvUpgrade\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Phobrv\BrvCore\Models\Option;
use Phobrv\BrvCore\Models\PostMeta;
use Phobrv\BrvCore\Repositories\OptionRepository;
use Phobrv\BrvCore\Repositories\PostRepository;
use Phobrv\BrvCore\Repositories\TermRepository;
use Phobrv\BrvCore\Repositories\UserRepository;
use Phobrv\BrvCore\Services\UnitServices;

class UpgradeController extends Controller
{

    protected $postRepository;
    protected $optionRepository;
    protected $termRepository;
    protected $userRepository;
    protected $unitService;

    public function __construct(
        PostRepository $postRepository,
        UserRepository $userRepository,
        OptionRepository $optionRepository,
        TermRepository $termRepository,
        UnitServices $unitService
    ) {
        $this->optionRepository = $optionRepository;
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->termRepository = $termRepository;
        $this->unitService = $unitService;
    }
    public function index()
    {

        $data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
            [
                ['text' => 'Upgrade data', 'href' => ''],
            ]
        );

        try {
            return view('phobrv::upgrade.index')->with('data', $data);
        } catch (Exception $e) {
            return back()->with('alert_danger', $e->getMessage());
        }
    }
    public function replace()
    {

        $data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
            [
                ['text' => 'Upgrade data', 'href' => ''],
            ]
        );

        try {
            return view('phobrv::upgrade.replaceDomain')->with('data', $data);
        } catch (Exception $e) {
            return back()->with('alert_danger', $e->getMessage());
        }
    }
    public function run(Request $request)
    {
        $requestData = $request->all();
        foreach ($requestData['choose'] as $value) {
            switch ($value) {
                case 'user':
                    $this->upgradeUser();
                    break;
                case 'post_group':
                    $this->updatePostGroup();
                    break;
                case 'post':
                    $this->updatePost();
                    break;
                case 'replace_post_content':
                    $this->replacePostContent();
                    break;
                case 'menu':
                    $this->insertMenu();
                    break;
                case 'update_menu_box_sidebar':
                    $this->updateMenuBoxSidebar();
                    break;
                case 'question':
                    $this->updateQuestion();
                    break;
                case 'config_web':
                    $this->insertConfigWeb();
                    break;
                case 'drugstore':
                    $this->insertDrugstore();
                    break;
                case 'video':
                    $this->insertVideo();
                    break;
                case 'album':
                    $this->insertAlbum();
                    break;
                case 'replace_domain':
                    $this->replaceDomain($requestData);
                    break;
            }
        }
        return redirect()->route('upgrade.index');
    }
    public function upgradeUser()
    {
        $users = DB::table('old_users')->get();
        foreach ($users as $u) {
            $tmp = [
                'id' => $u->id,
                'name' => $u->full_name,
                'password' => $u->password,
                'email' => $u->email,
                'avatar' => "public/img/" . $u->avatar,
            ];
            if (empty($this->userRepository->where('email', $u->email)->first())) {
                $this->userRepository->updateOrcreate($tmp);
            }
        }
    }
    public function updatePostGroup()
    {
        $post_groups = DB::table('old_post_group')->get();
        foreach ($post_groups as $pg) {
            $term = [
                'id' => $pg->id,
                'name' => $pg->name,
                'slug' => $pg->alias,
                'description' => $pg->description,
                'taxonomy' => 'category',
            ];
            DB::table('terms')->insert($term);
        }
    }
    public function insertMenu()
    {
        $term = [
            'name' => 'Main menu',
            'slug' => 'main-menu',
            'taxonomy' => config('term.taxonomy.menugroup'),
        ];
        $term = $this->termRepository->updateOrCreate($term);
        $menus = DB::table('old_menu')->where('parentmenu', '0')->get();

        foreach ($menus as $p) {
            $newMenu = $this->insertNewMenu($p, $term);
            $childs = DB::table('old_menu')->where('parentmenu', $p->id)->get();
            foreach ($childs as $mc) {
                $cmenu = $this->insertNewMenu($mc, $term);
                $this->postRepository->update(['parent' => $newMenu->id], $cmenu->id);
            }
        }
    }
    public function updatePost()
    {
        $posts = DB::table('old_posts')->get();
        foreach ($posts as $p) {
            $ck = $this->postRepository->findWhere(['slug' => $p->alias])->first();

            if (!$ck) {
                $ckauthor = $this->userRepository->findWhere(['id' => $p->author_id])->first();
                if (!$ckauthor) {
                    $p->author_id = 1;
                }

                $tmp = [
                    'user_id' => $p->author_id,
                    'title' => $p->title,
                    'slug' => $p->alias,
                    'content' => $p->content,
                    'thumb' => env('APP_URL') . '/storage/photos/shares/thumbs/' . $p->image_thumb,
                    'excerpt' => $p->summary,
                    'created_at' => $p->created_at,
                    'type' => 'post',
                ];
                $post = $this->postRepository->create($tmp);
                if ($p->group_id) {
                    $post->terms()->sync($p->group_id);
                }
                if (!empty($p->doctor)) {
                    $meta['doctor'] = 0;
                    $doctor = DB::table('old_author')->find($p->doctor);
                    if ($doctor) {
                        $dp = $this->postRepository->findWhere(['slug' => $doctor->alias])->first();
                        if ($dp) {
                            $meta['doctor'] = $dp->id;
                        }

                    }
                }
                if (!empty($p->pharmacist)) {
                    $meta['pharmacist'] = 0;
                    $doctor = DB::table('old_author')->find($p->pharmacist);
                    if ($doctor) {
                        $dp = $this->postRepository->findWhere(['slug' => $doctor->alias])->first();
                        if ($dp) {
                            $meta['pharmacist'] = $dp->id;
                        }
                    }
                }
                $meta['meta_title'] = ($p->meta_title) ? $p->meta_title : $p->title;
                $meta['meta_description'] = $p->meta_description;
                $meta['meta_keywords'] = $p->meta_keywords;
                $this->postRepository->insertMeta($post, $meta);
            }
        }
    }
    public function replaceDomain($data)
    {
        $posts = $this->postRepository->all();
        foreach ($posts as $p) {
            $content = str_replace($data['domain_old'], $data['domain_new'], $p->content);
            $thumb = str_replace($data['domain_old'], $data['domain_new'], $p->thumb);
            $this->postRepository->update(['content' => $content, 'thumb' => $thumb], $p->id);
        }
        $postMetas = PostMeta::all();
        foreach ($postMetas as $m) {
            $value = str_replace($data['domain_old'], $data['domain_new'], $m->value);
            $pm = PostMeta::find($m->id);
            $pm->value = $value;
            $pm->save();
        }
        $options = Option::all();
        foreach ($options as $o) {
            $value = str_replace($data['domain_old'], $data['domain_new'], $o->value);
            $o = Option::find($o->id);
            $o->value = $value;
            $o->save();
        }
    }
    public function insertAlbum()
    {
        $albums = DB::table('old_album')->get();
        foreach ($albums as $g) {
            $term = [
                'name' => $g->name,
                'slug' => $g->alias,
                'taxonomy' => 'album',
            ];
            $album = $this->termRepository->updateOrCreate($term);

            $imgs = DB::table('old_album_image')->where('album_id', $g->id)->get();
            foreach ($imgs as $v) {
                $alias = $this->unitService->renderSlug($v->title);
                $ck = $this->postRepository->findWhere(['slug' => $alias])->first();
                if ($ck) {
                    $alias = $alias . "-" . rand(10, 99);
                }

                $tmp = [
                    'user_id' => 1,
                    'title' => $v->title,
                    'excerpt' => $v->link,
                    'slug' => $alias,
                    'type' => 'image',
                    'thumb' => "public/img/" . $v->name,
                    'order' => isset($album->posts) ? (count($album->posts) + 1) : 1,
                ];
                $albumImage = $this->postRepository->create($tmp);
                $albumImage->terms()->sync($album->id);
            }

        }
    }
    public function insertVideo()
    {
        $groups = DB::table('old_video_group')->get();
        foreach ($groups as $g) {
            $term = [
                'name' => $g->name,
                'slug' => $this->unitService->renderSlug($g->name),
                'taxonomy' => 'video',
            ];
            $group = $this->termRepository->updateOrCreate($term);

            $videos = DB::table('old_video')->where('group_id', $g->id)->get();
            foreach ($videos as $v) {
                $ck = $this->postRepository->findWhere(['slug' => $v->alias])->first();
                if ($ck) {
                    $v->alias = $v->alias . "-" . rand(10, 99);
                }

                $tmp = [
                    'user_id' => 1,
                    'title' => $v->title,
                    'excerpt' => $v->id_video,
                    'slug' => $v->alias,
                    'type' => 'video',
                    'thumb' => $v->thumbnail_url,
                ];
                $video = $this->postRepository->create($tmp);
                $video->terms()->sync($group->id);
            }

        }
    }
    public function insertDrugstore()
    {
        $regions = DB::table('old_domain')->orderBy('stt')->get();
        foreach ($regions as $value) {
            $term = [
                'name' => $value->name,
                'slug' => $value->alias,
                'taxonomy' => 'region',
            ];
            $this->termRepository->updateOrCreate($term);

        }
        $all = $this->postRepository->findWhere(['type' => 'drugstore']);
        foreach ($all as $rd) {
            $this->postRepository->destroy($rd->id);
        }

        $regions = $this->termRepository->findWhere([
            'taxonomy' => 'region',
        ]);
        foreach ($regions as $region) {

            $drugstores = DB::table('old_point')->where('parent_id', '0')->where('mien', '=', $region->slug)->get();
            foreach ($drugstores as $ds) {
                $ck = $this->postRepository->findWhere(['slug' => $ds->alias])->first();
                if ($ck) {
                    $ds->alias = $product->alias . rand(10, 99);
                }

                $tmp = [
                    'user_id' => '1',
                    'title' => $ds->name,
                    'slug' => $ds->alias,
                    'content' => $ds->content,
                    'type' => 'drugstore',
                ];
                $meta['meta_title'] = $ds->name;
                $meta['meta_description'] = $ds->meta_description;
                $meta['meta_keywords'] = $ds->meta_keywords;
                $dParent = $this->postRepository->updateOrCreate($tmp);
                $this->postRepository->insertMeta($dParent, $meta);
                $dParent->terms()->sync($region->id);

                $childs = DB::table('old_point')->where('parent_id', $ds->id)->get();
                foreach ($childs as $c) {
                    $ck = $this->postRepository->findWhere(['slug' => $c->alias])->first();
                    if ($ck) {
                        $c->alias = $c->alias . "-" . rand(10, 99);
                    }

                    $tmp = [
                        'user_id' => '1',
                        'title' => $c->name,
                        'slug' => $c->alias,
                        'content' => $c->content,
                        'parent' => $dParent->id,
                        'type' => 'drugstore',
                    ];
                    $dc = $this->postRepository->updateOrCreate($tmp);
                    $meta['meta_title'] = $c->name;
                    $meta['meta_description'] = $c->meta_description;
                    $meta['meta_keywords'] = $c->meta_keywords;
                    $this->postRepository->insertMeta($dc, $meta);
                    $dc->terms()->sync($region->id);
                }

            }
        }
    }
    public function insertConfigWeb()
    {
        $pageValues = DB::table('old_page_value')->where('object', 'page_config')->get();
        foreach ($pageValues as $value) {
            $tmp = [
                'name' => $value->key,
                'value' => $value->value,
            ];
            $this->optionRepository->updateOrCreate($tmp);
        }
    }
    public function updateMenuBoxSidebar()
    {
        $menus = $this->postRepository->findWhere(['type' => 'menu_item']);
        foreach ($menus as $menu) {
            $mOld = DB::table('old_menu')->where('alias', $menu->slug)->get()->first();
            if ($mOld) {
                $pageValues = DB::table('old_page_value')->where('page_id', $mOld->id)->get();
                foreach ($pageValues as $pg) {
                    if ($pg->key == 'box_sidebar') {
                        $this->postRepository->insertMeta($menu, ['box_sidebar' => $pg->value], 'multi');
                    }
                }
            }
        }
    }

    public function updateQuestion()
    {
        $term = [
            'name' => 'Question group 1',
            'slug' => 'question-group1',
            'taxonomy' => config('term.taxonomy.questiongroup'),
        ];
        $term = $this->termRepository->updateOrCreate($term);

        $questions = DB::table('old_questions')->get();

        foreach ($questions as $p) {
            $ck = $this->postRepository->findWhere(['slug' => $p->alias])->first();
            if ($ck) {
                $p->alias = $p->alias . "-" . rand(10, 99);
            }

            $tmp = [
                'user_id' => '1',
                'title' => $p->title,
                'slug' => $p->alias,
                'excerpt' => $p->question,
                'content' => $p->answer,
                'type' => 'question',
                'thumb' => env('APP_URL') . '/storage/photos/shares/thumbs/' . $p->image,
            ];
            $ques = $this->postRepository->updateOrCreate($tmp);
            $ques->terms()->sync($term->id);
            $meta['meta_title'] = $p->title;
            $meta['meta_description'] = $p->meta_description;
            $meta['meta_keywords'] = $p->meta_keywords;
            $meta['summary'] = $p->summary;
            $this->postRepository->insertMeta($ques, $meta);
        }
    }

    public function replacePostContent()
    {
        $posts = $this->postRepository->all();
        foreach ($posts as $p) {
            $content = str_replace('http://vnphar.com/', 'http://linaphar.com/', $p->content);
            $this->postRepository->update(['content' => $content], $p->id);
        }
    }
    public function insertNewMenu($p, $term)
    {
        $ck = $this->postRepository->findWhere(['slug' => $p->alias])->first();
        if ($ck) {
            $p->alias = $p->alias . "-1";
        }

        $tmp = [
            'user_id' => '1',
            'title' => $p->name,
            'slug' => $p->alias,
            'type' => 'menu_item',
            'subtype' => $p->template,
            'order' => $p->stt,
        ];
        $menu = $this->postRepository->updateOrCreate($tmp);
        $menu->terms()->sync($term->id);
        $pageValues = DB::table('old_page_value')->where('page_id', $p->id)->get();
        foreach ($pageValues as $pg) {
            $meta[$pg->key] = $pg->value;
        }
        $meta['meta_title'] = $p->title;
        $meta['meta_description'] = $p->meta_description;
        $meta['meta_keywords'] = $p->meta_keywords;
        $this->postRepository->insertMeta($menu, $meta);

        return $menu;
    }
}
