<?php
/**
 * Created by PhpStorm.
 * User: tianyu
 * Date: 2017/7/24
 * Time: 17:24
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use AipOcr;

class BaiDuController extends Controller
{


    function IdCard(Request $request)
    {

        $client = new AipOcr(env('BAI_DU_SERVER_APP_ID'), env('BAI_DU_SERVER_API_KEY'), env('BAI_DU_SERVER_SECRET_KEY'));

        $image = file_get_contents('/Users/chunyu/Downloads/图片/正.jpg');//编码后大小不超过4M，最短边至少15px，最长边最大4096px,支持jpg/png/bmp格式
        $idCardSide = "front";//front  back

        // 调用身份证识别
        $res = $client->idcard($image, $idCardSide);

        // 如果有可选参数
        $options = [];
        $options["detect_direction"] = "true";//是否检测图像朝向，默认不检测，即：false。朝向是指输入图像是正常方向、逆时针旋转90/180/270度。可选值包括: - true：检测朝向； - false：不检测朝向。
        $options["detect_risk"] = "true";//是否开启身份证风险类型(身份证复印件、临时身份证、身份证翻拍、修改过的身份证)功能，默认不开启，即：false。可选值:true-开启；false-不开启


        // 带参数调用身份证识别
        $opt_res = $client->idcard($image, $idCardSide, $options);


        return response()->json($res);
    }

}