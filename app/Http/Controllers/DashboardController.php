<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\City;
use App\Models\ClientTrackList;
use App\Models\Configuration;
use App\Models\Message;
use App\Models\QrCodes;
use App\Models\TrackList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index ()
    {

        $qr = QrCodes::query()->select()->where('id', 1)->first();
        $qrChina = QrCodes::query()->select()->where('id', 2)->first();
        $qrKulager = QrCodes::query()->select()->where('id', 3)->first();
        $qrBek = QrCodes::query()->select()->where('id', 4)->first();
        /*$qrAstana = QrCodes::query()->select()->where('id', 6)->first();
        $qrTaraz = QrCodes::query()->select()->where('id', 5)->first();*/
        $config = Configuration::query()->select('address', 'title_text', 'address_two')->first();
        $cities = City::query()->select('title')->get();
        $messages = Message::all();
        $china_address = "china";
        if (isset(Auth::user()->branchinfo->china_address)){
            $china_address = Auth::user()->branchinfo->china_address;
        }
        if (Auth::user()->is_active === 1 && Auth::user()->type === null){
            $tracks = ClientTrackList::query()
                ->leftJoin('track_lists', 'client_track_lists.track_code', '=', 'track_lists.track_code')
                ->select('client_track_lists.track_code', 'client_track_lists.detail', 'client_track_lists.created_at', 'client_track_lists.id',
                    'track_lists.to_china', 'track_lists.to_almaty', 'track_lists.to_client', 'track_lists.to_city', 'track_lists.city',
                    'track_lists.city', 'track_lists.to_client_city', 'track_lists.client_accept', 'track_lists.status')
                ->where('client_track_lists.user_id', Auth::user()->id)
                ->where('client_track_lists.status',null)
                ->orderByDesc('client_track_lists.id')
                ->get();
            $count = count($tracks);
            return view('dashboard')->with(compact('tracks', 'count', 'messages', 'config', 'china_address'));
        }elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'stock'){
            $config = Configuration::query()->select('address', 'title_text', 'address_two')->first();
            $count = TrackList::query()->whereDate('created_at', Carbon::today())->count();
            return view('stock', ['count' => $count, 'config' => $config, 'qr' => $qrChina, 'china_address' => $china_address]);
        }elseif (Auth::user()->type === 'newstock') {
            $count = TrackList::query()->whereDate('created_at', Carbon::today())->count();
            return view('newstock')->with(compact('count', 'config', 'qr', 'china_address'));
        }elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'almatyin'){
            $config = Configuration::query()->select('address', 'title_text', 'address_two')->first();
            $count = TrackList::query()->whereDate('to_almaty', Carbon::today())->count();
            return view('almaty', ['count' => $count, 'config' => $config, 'cityin' => 'Алматы', 'qr' => $qr, 'china_address' => $china_address]);
        }elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'kulagerin') {
                $count = TrackList::query()->whereDate('to_city', Carbon::today())->where('status', 'Получено на складе в мкр.Кулагер 7')->count();
                return view('almaty', ['count' => $count, 'config' => $config, 'cityin' => 'мкр.Кулагер 7', 'qr' => $qrKulager, 'china_address' => $china_address]);
        }elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'bekin') {
                $count = TrackList::query()->whereDate('to_city', Carbon::today())->where('status', 'Получено на складе в ул.Бекмаханова 56а')->count();
                return view('almaty', ['count' => $count, 'config' => $config, 'cityin' => 'ул.Бекмаханова 56а', 'qr' => $qrBek, 'china_address' => $china_address]);
            /*}elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'astanain') {
                    $count = TrackList::query()->whereDate('to_city', Carbon::today())->where('status', 'Получено на складе в Астане')->count();
                    return view('almaty', ['count' => $count, 'config' => $config, 'cityin' => 'Астане', 'qr' => $qrAstana, 'china_address' => $china_address]);
            }elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'tarazin') {
                    $count = TrackList::query()->whereDate('to_city', Carbon::today())->where('status', 'Получено на складе в Таразе')->count();
                    return view('almaty', ['count' => $count, 'config' => $config, 'cityin' => 'Таразе', 'qr' => $qrTaraz, 'china_address' => $china_address]);
            */}elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'almatyout'){
            $config = Configuration::query()->select('address', 'title_text', 'address_two')->first();
            $count = TrackList::query()->whereDate('to_client', Carbon::today())->count();
            $cities = City::query()->select('title')->get();
            return view('almatyout', ['count' => $count, 'config' => $config, 'cities' => $cities, 'cityin' => 'Алматы', 'qr' => $qr, 'china_address' => $china_address]);
        } elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'kulagerout') {
            $count = TrackList::query()->whereDate('to_client_city', Carbon::today())->where('city', 'мкр.Кулагер 7')->count();
            return view('almatyout', ['count' => $count, 'config' => $config, 'cities' => $cities, 'cityin' => 'мкр.Кулагер 7', 'qr' => $qrKulager, 'china_address' => $china_address]);
        } elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'bekout') {
            $count = TrackList::query()->whereDate('to_client_city', Carbon::today())->where('city', 'ул.Бекмаханова 56а')->count();
            return view('almatyout', ['count' => $count, 'config' => $config, 'cities' => $cities, 'cityin' => 'ул.Бекмаханова 56а', 'qr' => $qrBek, 'china_address' => $china_address]);
        }/* elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'tarazout') {
            $count = TrackList::query()->whereDate('to_client_city', Carbon::today())->count();
            return view('almatyout', ['count' => $count, 'config' => $config, 'cities' => $cities, 'cityin' => 'Таразе', 'qr' => $qrTaraz, 'china_address' => $china_address]);
        }elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'astanaout') {
            $count = TrackList::query()->whereDate('to_client_city', Carbon::today())->count();
            return view('almatyout', ['count' => $count, 'config' => $config, 'cities' => $cities, 'cityin' => 'Астане', 'qr' => $qrAstana, 'china_address' => $china_address]);
        }*/elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'othercity'){
            $config = Configuration::query()->select('address', 'title_text', 'address_two')->first();
            $count = TrackList::query()->whereDate('to_client', Carbon::today())->count();
            $cities = City::query()->select('title')->get();
            return view('othercity')->with(compact('count', 'config', 'cities', 'qr', 'china_address'));
        }elseif (Auth::user()->is_active === 1 && Auth::user()->type === 'admin' || Auth::user()->is_active === 1 && Auth::user()->type === 'moderator'){
            $messages = Message::all();
            $config = Configuration::query()->select('address', 'title_text', 'address_two')->first();
            $search_phrase = '';
            $users = User::query()->select('id', 'name', 'surname', 'type', 'login', 'city', 'branch', 'code', 'is_active', 'block', 'password', 'created_at')->where('type', null)->where('is_active', false)->get();
            return view('admin')->with(compact('users', 'messages', 'search_phrase', 'config', 'china_address'));
        }
        $config = Configuration::query()->select('whats_app')->first();
        return view('register-me')->with(compact( 'config'));
    }

    public function archive ()
    {
        $tracks = ClientTrackList::query()
        ->leftJoin('track_lists', 'client_track_lists.track_code', '=', 'track_lists.track_code')
        ->select( 'client_track_lists.track_code', 'client_track_lists.detail', 'client_track_lists.created_at',
            'track_lists.to_china','track_lists.to_almaty','track_lists.to_client','track_lists.to_city','track_lists.city','track_lists.to_client_city','track_lists.client_accept','track_lists.status')
        ->where('client_track_lists.user_id', Auth::user()->id)
        ->where('client_track_lists.status', '=', 'archive')
        ->get();
        $config = Configuration::query()->select('address', 'title_text', 'address_two', 'whats_app')->first();
            $count = count($tracks);
            return view('dashboard')->with(compact('tracks', 'count', 'config'));
    }



}
