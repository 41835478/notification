<?php

namespace App\Http\Controllers\Admin;

use App\Payment\Alipay\Pay\Service\AlipayTradeService;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepositories;
use App\Repositories\RefundRepositories;
use App\Service\AlipayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlipayController extends Controller
{

    protected $request;
    protected $config;
    protected $alipay;
    protected $order;
    protected $refund;

    public function __construct(Request $request,
                                AlipayService $alipay,
                                OrderRepositories $order,
                                RefundRepositories $refund
    )
    {
        $this->request = $request;
        $this->alipay = $alipay;
        $this->order = $order;
        $this->refund = $refund;
    }

    /**
     * 跳转到支付宝网关付款
     * 过滤已经付款的订单
     *
     */
    public function pay()
    {
        $post = $this->request->all();
        $this->alipay->Pay($post);
    }

    /**
     * 接收返回数据并验证
     * 验证通过现在验证通过view
     * 否则显示失败view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function callback()
    {
        $callback = $this->request->all();

        if ($this->alipay->callback($callback)) {
            return view('payment.success', [
                'order' => $this->order->findOne('order_number', $callback['out_trade_no']),
                'callback' => $callback,
            ]);
        }

        return view('payment.faile', [
            'order' => $this->order->findOne('order_number', $callback['out_trade_no']),
            'callback' => $callback,
        ]);
    }


    /**
     * 查询订单付款状态
     *
     * @param $order_id
     * @return string
     */
    public function query($order_id)
    {
        $order = $this->order->findOne('order_id', $order_id);
        try {
            $query = $this->alipay->query($order);
        } catch (\Exception $e) {
            return response($e->getMessage());
        }

       dd($query);
    }

    /**
     * 接收支付宝主动发送的数据
     */
    public function app()
    {
        $alipaySevice = new AlipayTradeService();
        $app = $this->request->all();
        $result = $alipaySevice->check($app);
        if ($result) {
            if($app['trade_status'] == 'TRADE_FINISHED' || $app['trade_status'] == 'TRADE_SUCCESS') {
                //本地验证订单合法性
                $order_detail = $this->order->findOne('order_number', $app['out_trade_no']);
                if ($app['total_amount'] == $order_detail['total_amount'] &&
                    $app['seller_id'] == config('alipay.seller_id') &&
                    $app['app_id'] == config('alipay.app_id')
                ) {
                    $this->order->update('order_number', $app['out_trade_no'], [
                        'payment_type' => 'alipay',
                        'trade_no' => $app['trade_no'],
                        'payment_status' => 1
                    ]);
                    //成功记录到日志
                    Log::info('alipay_success_post:'.json_encode($app));
                    return response('success');
                }
            }
        }
        //验证失败记录到日志
        Log::info('alipay_faile_post:'.json_encode($app));
    }
}
