<?php

namespace App\Orchid\Screens;

use App\Models\Api\App;
use App\Models\Api\Link;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Platform\Models\Role;
use Orchid\Platform\Models\User;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Map;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Fields\Range;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\UTM;
use Orchid\Screen\Layouts\Legend;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Ramsey\Uuid\Uuid;

class LinkScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'links' => Link::orderBy('created_at', 'DESC')->paginate(30),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Ссылки';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Страница создания и просмотра ссылок';
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @throws \Throwable
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        $helpText = [
            '{os} - операционная система',
            '{country} - страна',
            '{cost} - стоимость',
            '{campaignid} - id кампании',
            '{SUBID} - id клика',
        ];

        $helpText = implode("</br>", $helpText);

        return [
            Layout::columns([

                Layout::rows([

                    Select::make('link')
                        ->options([
                            'os'      => 'os',
                            'country' => 'country',
                            'cost'    => 'cost',
                            'campaignid' => 'campaignid',
                            'clickid'    => 'SUBID',
                        ])
                        ->multiple()
                        ->required(true)
                        ->popover('Выберите параметры для формирования ссылки')
                        ->title('Параметры ссылки'),

//                    Input::make('template')
//                        ->title('Шаблон ссылки')
//                        ->type('url')
//                        ->help(env('APP_URL').'/hook?clickid=${SUBID}&country=${country}')
//                        ->popover('Введите шаблон для рекламодателя'),

                    Input::make('name')
                        ->title('Название ссылки')
                        ->required(true)
                        ->popover('Введите название для ссылки'),

                    Relation::make('app')
                        ->fromModel(App::class, 'name')
                        ->title('Приложение')
                        ->required(true),

                    Switcher::make('is_prelanding')
                        ->sendTrueOrFalse()
                        ->horizontal()
                        ->placeholder('Преленд'),

                    Input::make('prelanding_url')
                        ->title('Ссылка на преленд')
                        ->popover('Введите ссылку для перенаправления'),

                    Button::make('Сохранить')->method('save')
                        ->type(Color::DARK()),

                ]),

                Layout::legend('user', [
                    Sight::make('')->render(function () use ($helpText){
                        return $helpText;
                    }),
                ]),

//                Legend::make('links', [
//                    TD::make('id', 'ID')
//                        ->width('150')
//                        ->render(function (Repository $model) {
//                            // Please use view('path')
//                            return "<img src='https://picsum.photos/450/200?random={$model->get('id')}'
//                              alt='sample'
//                              class='mw-100 d-block img-fluid'>
//                            <span class='small text-muted mt-1 mb-0'># {$model->get('id')}</span>";
//                        }),

                ]),
            Layout::table('links', [
                TD::make('name')->width(70),
                TD::make('created_at')->render(function ($link) {
                    return Carbon::parse($link->created_at)->format('Y-m-d H:i:s');
                })->width(200)->defaultHidden(true),

                TD::make('body')->align(TD::ALIGN_CENTER)->width(400),
                TD::make('is_work')->align(TD::ALIGN_CENTER)->width(50)->defaultHidden(true),
                TD::make('is_prelanding')->align(TD::ALIGN_CENTER)->width(50),
                TD::make('prelanding_url')->align(TD::ALIGN_CENTER)->width(250),
            ]),
        ];
    }

    public function save(Request $request)
    {
        try {
            $uuid = Uuid::uuid4()->toString();
            $link = Link::query()->create([
                'app_id' => $request->app,
                'body'   => env('APP_URL')."/app/transition?uuid={$uuid}&".$this->buildBodyLink($request->toArray()['link']),
                'name'   => $request->name,
                'uuid'   => $uuid,
                'is_prelanding' => $request->is_prelanding,
                'prelanding_url' => $request->prelanding_url ?? null,
            ]);

            Alert::success('Ссылка успешно создана, ее uuid : '.$link->uuid);

        } catch (\Throwable $exception) {

            Toast::error('При создании возникла ошибка');
        }
    }

    /**
     * @param array $arrayParams ['os','country',...]
     * @return string 'os=${os}&country=...'
     */
    private function buildBodyLink(array $arrayParams): string
    {
        return implode('&', array_map(function ($param) {

            if($param != 'clickid') {

                return $param.'=${'.$param.'}';
            } else {
                return 'clickid=${SUBID}';
            }
        }, $arrayParams));
    }

    public function showToast(Request $request): void
    {
        Toast::warning($request->get('toast', 'Hello, world! This is a toast message.'));
    }
}
