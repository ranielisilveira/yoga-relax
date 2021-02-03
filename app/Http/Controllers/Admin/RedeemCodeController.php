<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\RedeemCode;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use League\Csv\Reader;
use League\Csv\Statement;
use SplFileObject;


class RedeemCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $redeem_codes = RedeemCode::with(['user'])->orderBy('user_id');

        if ($request->search) {
            $redeem_codes->where('code', 'LIKE', "%{$request->search}%");
        }

        return $redeem_codes->paginate((int)($request->length ?? 10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|unique:redeem_codes,code',
        ], []);

        try {

            RedeemCode::create($request->all());

            return response([
                'message' => trans('messages.created_success')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file:csv',
        ], []);

        try {

            $csv = Reader::createFromPath($request->file('file')->getPathName());
            $csv->setDelimiter(',');
            $stmt = Statement::create()->offset(0);

            $records = $stmt->process($csv);
            foreach ($records as $record) {
                RedeemCode::firstOrCreate([
                    'code' => $record[0]
                ]);
            }

            return response([
                'message' => trans('messages.created_success')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
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
            return RedeemCode::with(['user'])->findOrFail($id);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
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
        $this->validate($request, [
            'code' => 'required|unique:redeem_codes,code,' . $id . ',id',
        ], []);

        try {

            $redeem_code = RedeemCode::findOrFail($id);
            $redeem_code->code = $request->code;
            $redeem_code->save();

            return response([
                'message' => trans('messages.updated_success'),
                'redeem_codes' => $this->index(new Request),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
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

            $redeem_code = RedeemCode::findOrFail($id);

            if ($redeem_code->user) {
                throw new Exception(trans('messages.redeem_code_delete_not_allowed'));
            }

            $redeem_code->delete();

            return response([
                'message' => trans('messages.deleted_success'),
                'redeem_codes' => $this->index(new Request),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
