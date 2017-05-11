<?php
namespace Home\Controller;
use Think\Controller;
class GoodsController extends Controller {

    public function __construct()
    {
        parent::__construct();

        if(empty(session('member_id')))
        {
            $this->redirect('Login/index');
        }else{
            $member_group_id = session('member_group_id');
            $member_group_info = M('member_group')->where(['member_group_id'=>$member_group_id])->find();
            $this->assign('member_group_info',$member_group_info);
        }
    }

    public function goodsList()
    {
        $goods_lists = M('goods')->where('goods_status=1')->select();
        $this->assign('goods_lists',$goods_lists);
        $this->display();
    }

    public function addGoods()
    {
        $parent_cat_lists = M('category')->where(['parent_id'=>0])->order('sort_order asc')->select();
        $this->assign('parent_cat_lists',$parent_cat_lists);

        $brand_lists = M('brand')->select();
        $this->assign('brand_lists',$brand_lists);    

        $this->display();
    }

    public function saveGoods()
    {
        $parent_id = I('post.parent_id');
        $goods_model_no = I('post.goods_model_no');
        $goods_price = I('post.goods_price');
        $goods_storage = I('post.goods_storage');
        $goods_brand = I('post.goods_brand');

        if($parent_id == -1)
        {
            $this->error('parent category is empty',U('Goods/addGoods'));
        }else{
            $sub_cat_info = M('category')->where(['parent_id'=>$parent_id])->select();
            if(!empty($sub_cat_info))
            {
                if($goods_cat == -1)
                {
                    $this->error('sub category is empty',U('Goods/addGoods'));
                }else{
                    $data['goods_cat'] = $goods_cat;
                }
                
            }else{
                $data['goods_cat'] = $parent_id;
            }         
        }

        if(I('post.attr_choice')=='custom'){
            $data['goods_attr'] = I('post.custom_attribute');
        }else{
            $data['goods_attr'] = I('post.standard_attribute');
        }

        $data['goods_cat'] = $parent_id;
        $data['goods_model_no'] = $goods_model_no;
        $data['goods_price'] = $goods_price;
        $data['goods_storage'] = $goods_storage;
        $data['goods_brand'] = $goods_brand;
        $data['goods_status'] = 1;

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";

        // die;

        if(M('goods')->add($data))
        {
            $this->success('add goods success',U('Goods/goodsList'));
        }else{
            $this->error('add goods fail!',U('Goods/addGoods'));
        }     
   
    }

    public function editGoods()
    {
        $goods_id = I('get.goods_id');
        $goods_info = M('goods')->find($goods_id);
        $this->assign('goods_info',$goods_info); 

        $parent_cat_lists = M('category')->where(['parent_id'=>0])->order('sort_order asc')->select();
        $this->assign('parent_cat_lists',$parent_cat_lists);

        $goods_cat_info = M('category')->where(['cat_id'=>$goods_info['goods_cat']])->find();
        if($goods_cat_info['parent_id'] > 0)
        {
           $cat_lists = M('category')->where(['parent_id'=>$goods_cat_info['parent_id']])->select();

           foreach ($cat_lists as $cat_info) 
           {
               if($cat_info['cat_id'] == $goods_info['goods_cat'])
               {
                    $subcat_str .= "<option value='".$cat_info['cat_id']."' selected>".$cat_info['cat_name']."</option>";
               }else{
                    $subcat_str .= "<option value='".$cat_info['cat_id']."'>".$cat_info['cat_name']."</option>";
               }
           }
            $this->assign('parent_id',$goods_cat_info['parent_id']); 
            $this->assign('subcat_str',$subcat_str); 
        }else{
            $this->assign('parent_id',$goods_info['goods_cat']); 
        }

        $brand_lists = M('brand')->select();
        $this->assign('brand_lists',$brand_lists); 

        $goods_attr_lists = $goods_info['goods_attr'];
        $goods_attr_arr = explode(",", $goods_attr_lists);
        foreach ($goods_attr_arr as $goods_attr_id) 
        {
            $attr_info = M('attribute')->find($goods_attr_id);
            $attribute_group_arr[] = $attr_info['attr_group_id'];
        }

        foreach ($attribute_group_arr as $attr_group_id) 
        {
            $attr_group_info = M('attribute_group')->where(['attr_group_id'=>$attr_group_id])->find();
            $attr_lists = M('attribute')->where(['attr_group_id'=>$attr_group_id])->select();

            $attr_str .= "&nbsp&nbsp&nbsp&nbsp<strong>".$attr_group_info['attr_group_name'].": </strong>";
            foreach ($attr_lists as $attr_info) 
            {
                if(in_array($attr_info['attr_id'],$goods_attr_arr))
                {
                    $attr_str .= " <input type='radio' value=".$attr_info['attr_id']." name='attr_group_".$attr_group_id."' checked/> ".$attr_info['attr_values']." ";
                }else{
                   $attr_str .= " <input type='radio' value=".$attr_info['attr_id']." name='attr_group_".$attr_group_id."' /> ".$attr_info['attr_values']." "; 
                }
            }
        }
        $attr_str .= "<input type='hidden' name='attribute_group' id= 'attribute_group' value='".$cat_info['attribute_group']."'>";        
        $this->assign('attr_str',$attr_str); 

        $this->display(); 
    }

    public function deleteGoods(){

        $goods_id = I('get.goods_id');
        $data['goods_status'] = 0;

        if(M('goods')->where(['goods_id'=>$goods_id])->save($data))
        {
            $this->success('Delete goods success',U('Goods/goodsList'));
        }else{
            $this->error('Delete goods fail!',U('Goods/editGoods'));
        } 
    }

    public function updateGoods()
    {
        $goods_id = I('post.goods_id');
        $parent_id = I('post.parent_id');
        $goods_cat = I('post.goods_cat');
        $goods_price = I('post.goods_price');
        $goods_storage = I('post.goods_storage');
        $goods_brand = I('post.goods_brand');

        // if(!empty($attribute_group_lists))
        // {
        //     $attribute_group_arr = explode(',',$attribute_group_lists);
        //     foreach ($attribute_group_arr as $attr_group_id) 
        //     {
        //         $goods_attr[] = I('post.attr_group_'.$attr_group_id);
        //     }            
        // }

        if($parent_id == -1)
        {
            $this->error('parent category is empty',U('Goods/addGoods'));
        }else{
            $sub_cat_info = M('category')->where(['parent_id'=>$parent_id])->select();
            if(!empty($sub_cat_info))
            {
                if($goods_cat == -1)
                {
                    $this->error('sub category is empty',U('Goods/addGoods'));
                }else{
                    $data['goods_cat'] = $goods_cat;
                }
                
            }else{
                $data['goods_cat'] = $parent_id;
            }         
        }

        $data['goods_name'] = $goods_name;
        $data['goods_sku'] = $goods_sku;
        $data['goods_price'] = $goods_price;
        $data['goods_storage'] = $goods_storage;
        $data['goods_brand'] = $goods_brand;
        //$data['goods_attr'] = implode(',',$goods_attr);

        if(M('goods')->where(['goods_id'=>$goods_id])->save($data))
        {
            $this->success('update goods success',U('Goods/goodsList'));
        }else{
            $this->error('update goods fail!',U('Goods/editGoods'));
        }            
    }

    public function categoryList()
    {
        $category_lists = M('category')->order('parent_id asc')->select();
        $this->assign('category_lists',$category_lists);
        $this->display();
    }

    public function addCategory()
    {
        $parent_cat_lists = M('category')->where(['parent_id'=>0])->order('sort_order desc')->select();
        $this->assign('parent_cat_lists',$parent_cat_lists);

        $attr_group_lists = M('attribute_group')->select();
        $this->assign('attr_group_lists',$attr_group_lists);        

        $this->display();
    }

    public function saveCategory()
    {
        $parent_id = I('post.parent_id');
        $cat_name = I('post.cat_name');
        $sort_order = I('post.sort_order');

        $data['parent_id'] = $parent_id;
        $data['cat_name'] = $cat_name;
        $data['sort_order'] = $sort_order;

        if(M('category')->add($data))
        {
            $this->success('add category success',U('Goods/categoryList'));
        }else{
            $this->error('add category fail!',U('Goods/addCategory'));
        }       
    }

    public function editCategory()
    {
        $cat_id = I('get.cat_id');
        $parent_cat_lists = M('category')->where(['parent_id'=>0])->order('sort_order desc')->select();
        $this->assign('parent_cat_lists',$parent_cat_lists);
        $attr_group_lists = M('attribute_group')->select();
        $this->assign('attr_group_lists',$attr_group_lists);  

        $cat_info = M('category')->find($cat_id);

        $this->assign('cat_info',$cat_info);
        $this->display();
    }

    public function updateCategory()
    {
        $cat_id = I('post.cat_id');
        $parent_id = I('post.parent_id');
        $cat_name = I('post.cat_name');
        $sort_order = I('post.sort_order');
        $attribute_group_arr = I('post.attribute_group');

        $data['parent_id'] = $parent_id;
        $data['cat_name'] = $cat_name;
        $data['sort_order'] = $sort_order;
        $data['attribute_group'] = implode(',',$attribute_group_arr);

        if(M('category')->where(['cat_id'=>$cat_id])->save($data) !== False)
        {
            $this->success('edit category success',U('Goods/categoryList'));
        }else{
            $this->error('edit category fail!',U('Goods/editCategory'));
        }
    }

    public function deleteCategory()
    {
        $cat_id = I('get.cat_id');

        $sub_cat_info = M('category')->where(['parent_id'=>$cat_id])->find();

        if(!empty($sub_cat_info))
        {
            $this->error('Can not delete it which has subcategory!',U('Goods/categoryList'));
        }else{
            if(M('category')->where(['cat_id'=>$cat_id])->delete() !== False)
            {
                $this->success('delete category success',U('Goods/categoryList'));
            }else{
                $this->error('delete category fail!',U('Goods/categoryList'));
            }            
        }
    }

    public function getBrandsAjax()
    {
        $parent_id = I('get.parent_id');
        $brand_info = M('goods g')->query('select b.brand_name, g.goods_brand, b.brand_id 
                        from oms_goods g, oms_brand b where b.brand_id=g.goods_brand and g.goods_cat="'.$parent_id.'" group by goods_brand');


        if(!empty($brand_info))
        {
            echo "<option value='-1'> - Choose Brand - </option>";
            foreach ($brand_info as $brand_info) 
            {
                echo "<option value='".$brand_info['goods_brand']."'>".$brand_info['brand_name']."</option>";
            }
        }else{
            echo "<option value='-1'> - Choose Brand - </option>";
        }
    }

    public function getModelAjax()
    {
        $brand = I('get.brand');
        $model_info = M('goods')->field('goods_model_no, goods_brand')
        ->where(['goods_brand'=>$brand,'goods_status'=>1])
        ->group('goods_model_no')->select();


        if(!empty($model_info))
        {
            echo "<option value='-1'> - Choose Model - </option>";
            foreach ($model_info as $model_info) 
            {
                echo "<option value='".$model_info['goods_model_no']."'>".$model_info['goods_model_no']."</option>";
            }
        }else{
            echo "<option value='-1'> - Choose Model - </option>";
        }
    }

    public function getResultAjax()
    {
        $model = I('get.model');
        $brand = I('get.brand');
        $parent_id = I('get.parent_id');

        $goods_lists = M('goods')->where(['goods_model_no'=>$model,'goods_brand'=>$brand,'goods_cat'=>$parent_id])->select();

        // echo "<pre>";
        // print_r($goods_lists);
        // echo "</pre>";die;

        foreach ($goods_lists as $goods_info) 
        {

            $str .= "<tr>
                        <td>".getCategoryNameById($goods_info['goods_cat'])."</td>
                        <td>".getBrandNameById($goods_info['goods_brand'])."</td>
                        <td>".$goods_info['goods_model_no']."</td>
                        <td>".$goods_info['goods_price']."</td>";

            //if(getAttrNameById($goods_info['goods_attr'])==1 || getAttrNameById($goods_info['goods_attr'])==2){

                $str.="<td>".$goods_info['goods_attr']."</td>";
            // }else{
            //     $str.=" <td>".getAttrNameById($goods_info['goods_attr'])."</td>";
            // }
                /* This may change to allow multiple goods */
            $str.="     <td><input type='radio' name='goods_id' value='".$goods_info['goods_id']."'></td>
                    </tr>";
        }

        echo $str;
    }



    public function attributeList()
    {
        $Model = M();
        $attr_lists = $Model->table('macys_attribute')->join('macys_attribute_group ON  macys_attribute.attr_group_id = macys_attribute_group.attr_group_id')->order('macys_attribute_group.attr_group_id')->select();
        $this->assign('attr_lists',$attr_lists);
        $this->display();
    }

    public function addAttribute()
    {
        $attr_group_lists = M('attribute_group')->select();
        $this->assign('attr_group_lists',$attr_group_lists);

        $this->display();
    }

    public function saveAttribute()
    {
        $attr_group_id = I('post.attr_group_id');
        $attr_values = I('post.attr_values');

        $data['attr_group_id'] = $attr_group_id;
        $data['attr_values'] = $attr_values;

        if(M('attribute')->add($data))
        {
            $this->success('add attribute group success',U('Goods/attributeList'));
        }else{
            $this->error('add attribute group fail!',U('Goods/addAttribute'));
        }       
    }

    public function editAttribute()
    {
        $attr_id = I('get.attr_id');
        $Model = M();
        $attr_info = $Model->table('macys_attribute')->join('macys_attribute_group ON  macys_attribute.attr_group_id = macys_attribute_group.attr_group_id')->where(['attr_id'=>$attr_id])->find();
        $this->assign('attr_info',$attr_info);

        $attr_group_lists = M('attribute_group')->select();
        $this->assign('attr_group_lists',$attr_group_lists);

        $this->display();
    }

    public function updateAttribute()
    {
        $attr_id = I('post.attr_id');
        $attr_values = I('post.attr_values');

        if(M('attribute')->where(['attr_id'=>$attr_id])->save(['attr_values'=>$attr_values]) !== False)
        {
            $this->success('edit attribute success',U('Goods/attributeList'));
        }else{
            $this->error('edit attribute fail!',U('Goods/editAttribute'));
        }
 
    }

    public function deleteAttribute()
    {
        $attr_id = I('get.attr_id');

        if(M('attribute')->where(['attr_id'=>$attr_id])->delete() !== False)
        {
            $this->success('delete attribute success',U('Goods/attributeList'));
        }else{
            $this->error('delete attribute fail!',U('Goods/attributeList'));
        }
    }

    public function attributeGroupList()
    {
        $attr_group_lists = M('attribute_group')->select();
        $this->assign('attr_group_lists',$attr_group_lists);
        $this->display();
    }

    public function saveAttributeGroup()
    {
        $attr_group_name = I('post.attr_group_name');

        $data['attr_group_name'] = $attr_group_name;

        if(M('attribute_group')->add($data))
        {
            $this->success('add attribute group success',U('Goods/attributeGroupList'));
        }else{
            $this->error('add attribute group fail!',U('Goods/addAttributeGroup'));
        }       
    }

    public function editAttributeGroup()
    {
        $attr_group_id = I('get.attr_group_id');
        $attr_group_info = M('attribute_group')->where(['attr_group_id'=>$attr_group_id])->find();
        $this->assign('attr_group_info',$attr_group_info);
        $this->display();
    }

    public function updateAttributeGroup()
    {
        $attr_group_id = I('post.attr_group_id');
        $attr_group_name = I('post.attr_group_name');

        if(M('attribute_group')->where(['attr_group_id'=>$attr_group_id])->save(['attr_group_name'=>$attr_group_name]) !== False)
        {
            $this->success('edit attribute group success',U('Goods/attributeGroupList'));
        }else{
            $this->error('edit attribute group fail!',U('Goods/editAttributeGroup'));
        }
 
    }

    public function deleteAttributeGroup()
    {
        $attr_group_id = I('get.attr_group_id');

        if(M('attribute_group')->where(['attr_group_id'=>$attr_group_id])->delete() !== False)
        {
            $this->success('delete attribute group success',U('Goods/attributeGroupList'));
        }else{
            $this->error('delete attribute group fail!',U('Goods/attributeGroupList'));
        }
 
    }

    public function getAttributeDataAjax()
    {
        $cat_id = I('get.cat_id');
        $cat_info = M('category')->where(['cat_id'=>$cat_id])->find();

        $str = $cat_info['attribute_group'];

        // if(!empty($cat_info['attribute_group']))
        // {
        //     $attribute_group_arr = explode(',', $cat_info['attribute_group']);
        //     foreach ($attribute_group_arr as $attr_group_id) 
        //     {
        //         $attr_group_info = M('attribute_group')->where(['attr_group_id'=>$attr_group_id])->find();
        //         $attr_lists = M('attribute')->where(['attr_group_id'=>$attr_group_id])->select();

        //         $str .= "&nbsp&nbsp&nbsp&nbsp<strong>".$attr_group_info['attr_group_name'].": </strong>";
        //         foreach ($attr_lists as $attr_info) 
        //         {
        //             $str .= " <input type='radio' value=".$attr_info['attr_id']." name='attr_group_".$attr_group_id."' /> ".$attr_info['attr_values']." ";
        //         }
        //     }
        //     $str .= "<input type='hidden' name='attribute_group' id= 'attribute_group' value='".$cat_info['attribute_group']."'>";
        // }
 
        echo $str;   
    }

    public function brandList()
    {
        $brand_lists = M('brand')->select();
        $this->assign('brand_lists',$brand_lists);

        $this->display();
    }

    public function saveBrand()
    {
        $brand_name = I('post.brand_name');

        $data['brand_name'] = $brand_name;

        if(M('brand')->add($data))
        {
            $this->success('add brand success',U('Goods/brandList'));
        }else{
            $this->error('add brand fail!',U('Goods/addBrand'));
        }       
    }

    public function editBrand()
    {
        $brand_id = I('get.brand_id');
        $brand_info = M('brand')->where(['brand_id'=>$brand_id])->find();
        $this->assign('brand_info',$brand_info);
        $this->display();
    }

    public function updateBrand()
    {
        $brand_id = I('post.brand_id');
        $brand_name = I('post.brand_name');

        if(M('brand')->where(['brand_id'=>$brand_id])->save(['brand_name'=>$brand_name]) !== False)
        {
            $this->success('edit brand success',U('Goods/brandList'));
        }else{
            $this->error('edit brand fail!',U('Goods/editBrand'));
        }
 
    }

    public function deleteBrand()
    {
        $brand_id = I('get.brand_id');

        if(M('brand')->where(['brand_id'=>$brand_id])->delete() !== False)
        {
            $this->success('delete brand success',U('Goods/brandList'));
        }else{
            $this->error('delete brand fail!',U('Goods/brandList'));
        }
 
    }

}