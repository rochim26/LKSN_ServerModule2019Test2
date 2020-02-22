<?php

namespace App\Http\Controllers;

// Model
use App\Board;
use App\User;
use App\Card;
use App\BoardList;
use App\BoardMember;
use App\LoginToken;
use App\Image;

// Resource
use App\Http\Resources\BoardResource;

// Utils
use Illuminate\Http\Request;
use Validator;

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $boards = Board::get();

        return response()->json($boards, 200);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'invalid field'
            ], 422);
        }

        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;

        $board = Board::create([
            'name' => $request->name,
            'creator_id' => $user_id
        ]);

        BoardMember::create([
            'board_id' => $board->id,
            'user_id' => $user_id
        ]);

        return response()->json([
            'message' => 'create board success'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function show(Board $board, Request $request)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $board = Board::with(['boardLists' => function ($q)
        {
            $q->orderBy('order', 'asc');
            $q->with(['cards' => function ($q)
            {
                $q->orderBy('order', 'asc');
            }]);
        }])->with('boardMembers')->where('id', $board->id)->first();

        return new BoardResource($board);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function edit(Board $board)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Board $board)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'invalid field'
            ], 422);
        }

        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;

        $board->update([
            'name' => $request->name,
            'creator_id' => $user_id
        ]);

        return response()->json([
            'message' => 'update board success'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function destroy(Board $board, Request $request)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $creator_id = Board::whereCreatorId($user_id)->first();

        if(empty($creator_id)) {
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }
        
        $boardMembers = BoardMember::whereBoardId($board->id);
        $boardMembers->delete();
        $board->delete();

        return response()->json([
            'message' => 'delete board success'
        ], 200);
    }

    public function addTeamMember(Board $board, Request $request)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $user = User::whereUsername($request->username)->first();

        if(empty($user)){
            return response()->json([
                'message' => 'user did not exist'
            ], 422);
        }

        $check_member = BoardMember::where([
            ['user_id', $user->id],
            ['board_id', $board->id]
        ])->first();

        if($check_member) {
            return response()->json([
                'message' => 'user already exist'
            ], 422);
        }

        BoardMember::create([
            'user_id' => $user->id,
            'board_id' => $board->id
        ]);

        return response()->json([
            'message' => 'create member success'
        ], 200);
    }

    public function removeTeamMember(Board $board, Request $request, BoardMember $member)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $member->delete();

        return response()->json([
            'message' => 'remove member success'
        ], 200);
    }

    public function addList(Board $board, Request $request) 
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'invalid field'
            ], 422);
        }

        $highOrder = BoardList::max('order') ?? 0;

        BoardList::create([
            'name' => $request->name,
            'board_id' => $board->id,
            'order' => $highOrder + 1
        ]);

        return response()->json([
            'message' => 'create list success'
        ], 200);
    }

    public function updateList(Board $board, Request $request, BoardList $list) 
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'invalid field'
            ], 422);
        }

        $list->update([
            'name' => $request->name,
            'board_id' => $board->id
        ]);

        return response()->json([
            'message' => 'update list success'
        ], 200);
    }

    public function deleteList(Board $board, Request $request, BoardList $list) 
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $list->delete();

        return response()->json([
            'message' => 'delete list success'
        ], 200);
    }

    public function moveToRightList(Board $board, Request $request, BoardList $list) 
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $nextList = BoardList::whereOrder($list->order + 1)->first();

        $nextList->decrement('order');
        $list->increment('order');

        return response()->json([
            'message' => 'move success'
        ], 200);
    }

    public function moveToLeftList(Board $board, Request $request, BoardList $list) 
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $prevList = BoardList::whereOrder($list->order - 1)->first();

        $prevList->increment('order');
        $list->decrement('order');

        return response()->json([
            'message' => 'move success'
        ], 200);
    }

    public function addCard(Board $board, BoardList $list, Request $request)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'task' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'invalid field'
            ], 422);
        }

        $highOrder = Card::max('order') ?? 0;

        Card::create([
            'task' => $request->task,
            'list_id' => $list->id,
            'order' => $highOrder + 1
        ]);

        return response()->json([
            'message' => 'create card success'
        ], 200);
    }

    public function updateCard(Board $board, BoardList $list, Card $card, Request $request)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'task' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'invalid field'
            ], 422);
        }

        $highOrder = Card::max('order') ?? 0;

        $card->update([
            'task' => $request->task,
        ]);

        return response()->json([
            'message' => 'update card success'
        ], 200);
    }

    public function deleteCard(Board $board, BoardList $list, Card $card, Request $request)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
            ['board_id', $board->id]
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }
        
        $card->delete();

        return response()->json([
            'message' => 'delete card success'
        ], 200);
    }

    public function moveUpCard(Card $card, Request $request)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $currentCard = Card::whereOrder($card->order)->first();

        $cardMax = Card::where([
            ['order', '<', $currentCard->order],
            ['list_id', $currentCard->list_id]
            ])->latest()->first();

        $currentOrder = $currentCard->order;
        $currentMaxOrder = $cardMax->order;

        $currentCard->order = $currentMaxOrder;
        $cardMax->order = $currentOrder;

        $currentCard->save();
        $cardMax->save(); 

        return response()->json([
            'message' => 'move success'
        ], 200);
    }

    public function moveDownCard(Card $card, Request $request)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $currentCard = Card::whereOrder($card->order)->first();

        $cardMin = Card::where([
            ['order', '>', $currentCard->order],
            ['list_id', $currentCard->list_id]
            ])->first();

        $currentOrder = $currentCard->order;
        $currentMaxOrder = $cardMin->order;

        $currentCard->order = $currentMaxOrder;
        $cardMin->order = $currentOrder;

        $currentCard->save();
        $cardMin->save(); 

        return response()->json([
            'message' => 'move success'
        ], 200);
    }

    public function moveCardToAnotherList(Card $card, BoardList $list, Request $request)
    {
        $user_id = LoginToken::whereToken($request->bearerToken())->first()->user_id;
        $board_member = BoardMember::where([
            ['user_id', $user_id],
        ])->first();

        if(empty($board_member)){
            return response()->json([
                'member' => 'unauthorized user'
            ], 401);
        }

        $card->update([
            'list_id' => $list->id
        ]);

        return response()->json([
            'message' => 'move success'
        ], 200);
    }

    public function image(Request $request)
    {
        $image = Image::create([
            'image' => $request->image->store('uploads', 'public')
        ]);

        return $image;
    }

    public function imageShow()
    {
        $images = Image::get();

        return $images;
    }
}
