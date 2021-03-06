<?php
namespace App\Modules\Manage\Service;

use App\Models\AdminMember;
use App\Common\Msg;
use App\Common\Code;

/**
 * Email
 * @author Chengcheng
 * @date 2016-10-19 15:50:00
 */
class EmailService
{
    /**
     * 用户登录-通过Email-Password
     * @author Chengcheng
     * @param array $requestData
     * @return array
     */
    public static function loginEmail($requestData)
    {
        //0 预制返回结果
        $result         = array();
        $result["code"] = Code::SYSTEM_ERROR;
        $result["msg"]  = Msg::SYSTEM_ERROR;
        $result["data"] = "";

        //1 判断邮箱是否存在
        $member = AdminMember::model()->where('email', $requestData['email'])->first();
        if (!$member) {
            $result["code"] = Code::USER_EMAIL_ERROR;     //手机号码不存在
            $result["msg"]  = Msg::USER_EMAIL_ERROR;
            return $result;
        }

        //2 验证密码是否正确
        if (!password_verify($requestData['passwd'], $member->passwd)) {
            $result["code"] = Code::USER_PASSWORD_ERROR;
            $result["msg"]  = Msg::USER_PASSWORD_ERROR;
            return $result;
        }

        //3 保存用户登录信息
        $member->login($requestData['visitIP'], $requestData['visitTime']);

        //4 提取用户信息-扩展信息等（微信，分组，权限等）
        $memberInfo = $member->getMemberInfo();

        //5 登录成功，返回结果
        $result["code"] = Code::SYSTEM_OK;
        $result["msg"]  = Msg::USER_LOGIN_OK;
        $result["data"] = $memberInfo;
        return $result;
    }

    /**
     * 重置密码-token方式
     * @author Chengcheng
     * @date 2016-10-19 15:50:00
     * @param array $requestData
     * @return array
     */
    public static function resetPasswordByOld($requestData)
    {
        //0 预制返回结果
        $result         = array();
        $result["code"] = Code::SYSTEM_ERROR;
        $result["msg"]  = Msg::SYSTEM_ERROR;

        //1 验证旧密码是否正确
        $member = AdminMember::find($requestData['member_id']);
        if (empty($member) || !password_verify($requestData['old_passwd'], $member->passwd)) {
            $result["code"] = Code::USER_PASSWORD_ERROR;
            $result["msg"]  = Msg::USER_PASSWORD_ERROR;
            return $result;
        }

        //2 修改密码
        $member->passwd = password_hash($requestData['new_passwd'], PASSWORD_DEFAULT);
        $member->save();

        //3 修改成功
        $result["code"] = Code::SYSTEM_OK;
        $result["msg"]  = Msg::SYSTEM_OK;
        return $result;
    }
}
