<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        //引用模板,使用MVC中的assign和display方法;

        $this->display('Index/index');//模板默认引用和方法名同名的模板;
    }

    //超市模板;
    public function shop()
    {
        //查询默认地址,传到模板显示;
        $uid = session('uid');
        $address = M('Address');
        $row = $address->where(['uid'=>$uid])->where(['default'=>1])->find();
        $defaultadd = $row['address'];
        $this->assign('address',$defaultadd);

        $this->display('Index/shop');

    }

    //热食;
    public function tof()
    {

        //查询默认地址,传到模板显示;
        $uid = session('uid');
        $address = M('Address');
        $row = $address->where(['uid'=>$uid])->where(['default'=>1])->find();
        $defadd = $row['address'];
        $this->assign('address',$defadd);

            //查询熟食数据表,传到模板显示;
            $food = M('Hotfood');
            $rows = $food->select();
            $this->assign('rows',$rows);

        $this->display('Index/tof');

    }

    //轮播详情;
    public function bannerDetail()
    {

        $this->display('Index/bannerDetail');

    }

    //地址;
    public function address()
    {

        $this->display('Index/address');

    }

    //购物车;
    public function cart()
    {
        $uid = session('uid');
        //从数据库中查询此用户添加到购物车的商品列表;
        $cart = M('Cart as c');
        //把cart 和 hotfood 联查一下,组成一个数组传递到购物车模板;
        $rows = $cart->join('hotfood as h on c.gid = h.id')->where(['uid'=>$uid])->select();
        //遍历下数组rows计算出总价传到模板;
        $totalPrice = 0;
        foreach ($rows as $v){
            $totalPrice += $v['price']*$v['num'];
        }
        $this->assign('totalPrice',$totalPrice);
        $this->assign('rows',$rows);

        //调出收货人和默认地址收获地址;
        $address = M('Address');
        $row = $address->where(['uid'=>$uid])->where(['default'=>1])->find();
        //拆分一下地址信息,传递一个简洁的信息给模板第一条显示;
        $str = explode('/',$row['address']);
        $this->assign('str',$str[3]);
        $this->assign('row',$row);

        $this->display('Index/cart');

    }

    //会员;
    public function member()
    {
        //判断是否登录;
        $uid = session('uid');
        if($uid){
            $this->assign('uid',$uid);
        }else{
            $this->assign('uid',0);
        }
        //登录后显示内容;
        $member = M('Member');
        $row = $member->where(['id'=>$uid])->find();
        $this->assign('username',$row['username']);
        $this->assign('avatar',$row['avatar']);
        $t = time();
        $this->assign('t',$t);


        $this->display('Index/member');

    }

    //订单;
    public function order()
    {

        $this->display('Index/order');

    }

    //积分明细;
    public function memberPoints()
    {

        $this->display('Index/memberPoints');

    }

    //客服反馈;
    public function comments()
    {

        $this->display('Index/comments');

    }

    //收货地址修改;
    public function edit_address()
    {

        $this->display('Index/edit_address');

    }

    //账户设置;
    public function setting()
    {

        $this->display('Index/setting');

    }

    //登录;
    public function login()
    {

        $this->display('Index/login');

    }

    //登录处理;
    public function loginAction()
    {
        $username = I('post.username');
        $password =  md5(I('post.password'));

        $member = M('Member');
        $bool = $member->where(['username'=>$username])->where(['password'=>$password])->find();

        if($bool){
            //查询正确,登录成功 ;
            //获取用户id 把用户id存入session;
            $uid = $bool['id'];
            session('uid',$uid);

            return redirect('index');

        }else{
            //登录失败;

            return back();
        }

    }

    //退出;
    public function logout()
    {
        //查看参数是否能正常传输;
        $t = I('get.t');

        if($t == 0){
            //没问题,可以退出 清空session;
            session(null);

            $data['status'] = 1;
        }else{
            $data['status'] = 0;
        }

        $this->ajaxReturn($data);

    }

    //注册;
    public function register()
    {
        $this->display('Index/register');
    }

    //注册处理;
    public function registerAction()
    {

        $data['username'] = I('post.username');
        $data['password'] = md5(I('post.password1'));
        $data['mobile'] = I('post.mobile');
        //设置默认头像;
        $data['avatar'] = 'images/tou.png';
        //设置默认生日;
        $data['birthday'] = '2000-01-01';
        $member = M('Member');
        $bool = $member->add($data);
        if($bool){
            //注册成功;
            $url = U('login');
            $this->success('注册成功',$url,3);
        }

    }

    //头像;
    public function upload()
    {
        //接收传来的文件;
        $uid = session('uid');
        $pic = $_FILES['pic'];
        switch ($pic['type']){
            case 'image/jpeg':
                $set = 'jpg';
                break;
            case 'image/gif':
                $set = 'gif';
                break;
            case 'image/png';
                $set = 'png';
                break;
        }
        $rand = mt_rand(00000000,99999999);
        $newName = $rand.".".$set;
        $fileName = $_SERVER['DOCUMENT_ROOT'].'/Public/Home/images/'.$newName;
//        $filename = $_SERVER['DOCUMENT_ROOT'];//直至这些文件的最外层文件夹:lanchong;
        //文件路径
        $temname = $pic['tmp_name'];

        //把文件保存至路径,以1.jpg的文件名;
        $bool = move_uploaded_file($temname,$fileName);

        if($bool){
            //写一个方便在模板取出的路径;
            $path = 'images/'.$newName;
            //上传成功,修改该用户头像路径;
            $member = M('Member');
                //在数据库改变之前,先获得原来的头像图片的名称;
                $befor = $member->where(['id'=>$uid])->find();
                $befor1 = $befor['avatar'];
            $cool = $member->where(['id'=>$uid])->save(['avatar'=>$path]);
            if($cool){
                //新图片上传成功,删除原图;
                $link = $_SERVER['DOCUMENT_ROOT'].'/Public/Home/'.$befor1;
                unlink($link);

            }
        }
    }

    //个人信息;
    public function perinfo()
    {
        $uid = session('uid');

        $member = M('Member');
        $bool = $member->where(['id'=>$uid])->find();
        $a = $bool['avatar'];
        $username = $bool['username'];
        $this->assign('avatar',$a);
        $this->assign('username',$username);
        $this->assign('birthday',$bool['birthday']);

        $this->display('Index/perinfo');

    }

    //修改用户名;
    public function saveName()
    {
        $uid = session('uid');
        $member = M('Member');
        $bool = $member->where(['id'=>$uid])->find();
        $username = $bool['username'];
        $this->assign('username',$username);

        $this->display('Index/saveName');

    }

    //修改用户名处理方法;
    public function saveNameAction()
    {
        $uid = session('uid');
        $username = I('post.username');

        $member = M('Member');
        $bool = $member->where(['id'=>$uid])->save(['username'=>$username]);

        if($bool){
            //修改成功;
            return redirect('perinfo');
        }else{
            //修改失败;
            return back();
        }


    }

    //选择生日日期;
    public function chooseDay()
    {

        $this->display('Index/chooseDay');

    }

    //选择生日日期处理;
    public function chooseDayAction()
    {
        //获取uid;
        $uid = session('uid');
        //接收form传来的日期;
        $day = I('post.USER_AGE');//把年月日以 xxxx-xx-xx 字符串形式传递过来;

        //直接修改数据表中的日期;
        $member = M('Member');

        $bool = $member->where(['id'=>$uid])->save(['birthday'=>$day]);

        if($bool){
            //修改成功;
            return redirect('perinfo');
        }else{
            //修改失败;
            return back();
        }

    }

    //地址列表;
    public function addressList()
    {
        $uid = session('uid');
        //从数据库中查找该用户的地址;
        $address = M('Address');
        //获取数据库中用户的地址信息,最多取出5条显示;
        $row = $address->where(['uid'=>$uid])->limit(5)->select();
        $this->assign('row',$row);

        $this->display('Index/addressList');

    }

    //新建地址;
    public function chooseAddress()
    {

        //先查看此用户有没有地址;
        //没有,调用新建地址模板;

        $this->display('Index/chooseAddress');

    }

    //添加地址;
    public function addAddress()
    {
        $uid = session('uid');
        //接收地址数据;
        $address = I('post.address');
        $street = I('post.street');
        $data['uid'] = $uid;
        $data['address'] = $address."/".$street;
        $data['phone'] = I('post.phone');
        $data['name'] = I('post.person');

        $siet = M('Address');
        $bool = $siet->where(['uid'=>$uid])->add($data);

        if($bool){

            //添加地址成功;
            return redirect('addressList');
        }else{

            return back();
        }

    }

    //删除地址;(删除还是要有询问框的好)
    public function delAddress()
    {

        $id = I('get.id');

        $adr = M('Address');

        $bool = $adr->where(['id'=>$id])->delete();

        if($bool){
            //删除成功;
            $data['status'] = 1;
        }else{
            //删除失败;
            $data['status'] = 0;
        }
        $this->ajaxReturn($data);
    }

    //换绑手机号;
    public function changePhone()
    {
        $uid = session('uid');

        $member = M('Member');
        $row = $member->where(['id'=>$uid])->find();
        $mobile = $row['mobile'];
        $this->assign('mobile',$mobile);

        $this->display('Index/changePhone');

    }

    //发送短信;
    public function sendNote()
    {
        $uid = session('uid');
        //获取此用户的手机号;
        $member = M('Member');
        $row = $member->where(['id'=>$uid])->find();
        $mobile = $row['mobile'];
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $str = mt_rand(000000,999999);
        //验证短信存入session;
        session('str',$str);
        $smsConf = array(
            'key'   => '630a9982c2c06fc9f3a9697feddbacd5', //您申请的APPKEY
            'mobile'    => $mobile, //接受短信的用户手机号码
            'tpl_id'    => '161458', //您申请的短信模板ID，根据实际情况修改
            'tpl_value' =>"#code#={$str}&#company#=聚合数据" //您设置的模板变量，根据实际情况修改
        );

        $content = juhecurl($sendUrl,$smsConf,1); //请求发送短信

        if($content){
            $result = json_decode($content,true);
            $error_code = $result['error_code'];
            if($error_code == 0){
                //状态为0，说明短信发送成功
                $data['status'] = 1;
            }else{
                //状态非0，说明失败
               $data['status'] = 0;
            }
        }else{
            //返回内容异常，以下可根据业务逻辑自行修改
            $data['status'] = -1;
        }

        $this->ajaxReturn($data);

    }

    //验证短信;
    public function checkout()
    {
        //发送的验证码;
        $str = session('str');
        //用户输入的验证码;
        $verify = I('post.verify');
        //用户要换绑的手机号;
        $phone = I('post.phone');

        if($str == $verify){
            //验证成功,可以换绑手机号了;
            $uid = session('uid');
            $member = M('Member');
            $bool = $member->where(['id'=>$uid])->save(['mobile'=>$phone]);
            if($bool){
                //修改成功;
                $this->success('更换成功','changePhone',3);
            }else{
                $this->error('更换失败');
            }
        }else{
            $this->error('验证码不正确');
        }
    }

    //设置密码;
    public function setPassword()
    {

        $this->display('Index/setPassword');

    }

    //设置密码处理;
    public function setPasswordAction()
    {
        $uid = session('uid');
        //接收form表单传来的值;
        $original = md5(I('post.password'));
        $newpass = md5(I('post.password1'));
        $member = M('Member');
        $row = $member->where(['id'=>$uid])->find();
        //获取旧密码;
        $oldpass = $row['password'];

        //如果用户输入的原始密码==旧密码,就允许用户修改密码;
        if($original == $oldpass){
            //允许修改密码;
            $bool = $member->where(['id'=>$uid])->save(['password'=>$newpass]);
            if($bool){
                //修改成功;
                $this->success('密码修改成功','setting',3);
            }else{
                $this->error('修改失败');
            }
        }else{
            $this->error('原始密码不正确');
        }
    }

    //默认地址;
    public function defaultadd()
    {
        $address = M('Address');
        //先把default=1的字段改变为default=0;
        $row = $address->where(['default'=>1])->find();
        if($row){
            //如果有,就修改为0;
            $address->where(['default'=>1])->save(['default'=>0]);
        }

        $id = I('get.id');
        $bool = $address->where(['id'=>$id])->save(['default'=>1]);
        if($bool){
            //修改成功;
            $data['status'] = 1;
        }else{
            //修改失败;
            $data['status'] = 0;
        }
        $this->ajaxReturn($data);

    }

    //购物车增加数量;
    public function addNum()
    {
        //用户id;
        $uid = session('uid');
        //商品id;
        $id = I('get.id');

        $cart = M('Cart');
        //首先判断 该用户 的购物车中有没有 此商品 ;
        $bool = $cart->where(['uid'=>$uid])->where(['gid'=>$id])->find();
        if($bool){
            //购物车中已经有这个商品了,只需改变数量即可;
            //获取此商品的数量并+1;
            $num = $bool['num']+1;
            $cool = $cart->where(['uid'=>$uid])->where(['gid'=>$id])->save(['num'=>$num]);
        }else{
            //该用户的购物车中还没有次商品
            //添加次商品到该用户的购物车;
            $data['uid'] = $uid;
            $data['gid'] = $id;
            $data['num'] = 1;
            $cart->add($data);
        }


    }

    //购物车减少数量;
    public function reduceNum()
    {
        //用户id;
        $uid = session('uid');
        //商品id;
        $id = I('get.id');

        $cart = M('Cart');
        //首先判断该用户的购物车中有没有此商品;
        $bool = $cart->where(['uid'=>$uid])->where(['gid'=>$id])->find();
        if($bool){
            //有此商品,再判断此商品的数量是否大于零;
            if($bool['num'] > 0){
                //商品数量大于零,去除数量并-1;
                $num = $bool['num']-1;
                $cart->where(['uid'=>$uid])->where(['gid'=>$id])->save(['num'=>$num]);
            }
            //从新取一下商品数量判断是否=0,如果=0就从购物车中删除;
            $goodNum = $cart->where(['uid'=>$uid])->where(['gid'=>$id])->find();
            //这里要用 == 才行(=不行的);
            if($goodNum['num'] == 0){
                //商品数量等于零,从购物车删除该商品;
                $cart->where(['uid'=>$uid])->where(['gid'=>$id])->delete();
            }

        }

    }

    //支付页面;
    public function play()
    {
        //获取用户的留言备注,存到session;
        $remark = I('get.remark');
        session('remark',$remark);
        //获取总价格,存到session;
        $totalPrice = I('get.totalPrice');
        session('totalPrice',$totalPrice);

        if(session('totalPrice')){
            $data['status'] = 1;
        }else{
            $data['status'] = 0;
    }

        $this->ajaxReturn($data);
    }

    //支付处理页面;
    public function playAction()
    {
        $uid = session('uid');
        //从session中取出用户备注和总价,传递到支付页面;
        $remark = session('remark');
        $totalPrice = session('totalPrice');//(价格经过session应该不安全!!)
        $this->assign('remark',$remark);
        $this->assign('totalPrice',$totalPrice);

        //默认收获地址 收货人 联系电话;
        $address= M('Address');
        $row = $address->where(['uid'=>$uid])->where(['default'=>1])->find();
        $this->assign('row',$row);

        //生成订单编号,保存数据库并传递至模板;
        $time = time();
        $str = mt_rand(000000,999999);
        $orderNum = $time.$str;
        //订单号传递到模板;
        $this->assign('orderNum',$orderNum);
        //把订单号保存至数据库;
        //订单表需要:商品id 用户id 下单时间,订单中有哪些商品,每个商品的数量是多少;
        //问题:一个用户有多个订单,怎么区分每个订单的内容;
        //订单表加一个字段,区别不同的订单,(订单号???)
        //method: 一次购买的所有商品用同一个订单号,记录每种商品的id和数量;
        //(积分问题:付过款之后再增加用户本次消费赠送的积分)

        $this->display('Index/pay');

    }








}