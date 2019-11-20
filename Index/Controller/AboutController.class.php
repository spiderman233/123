<?php
//dezend by http://www.yunlu99.com/
namespace index\Controller;

class AboutController extends \Think\Controller
{
	public function _initialize()
	{
	}

	public function index()
	{
		$this->display();
	}

	public function lists()
	{
		$id = getValue('id', 'int');

		if (empty($id)) {
			msg('系统忙碌！', 2, U('index'));
		}

		$data = getData('article_type', 1, 'id=\'' . $id . '\'');
		$name = $data['name'];
		$article = getData('article', 'all', 'type=\'' . $id . '\' and `show`=1', '', 'id desc');
	//var_dump($data);
		if (count($article) == 1) {
			$aid = $article[0]['id'];
			header('Location:' . U('details', 'id=' . $aid));
		}

		$this->assign('id', $id);
		$this->assign('name', $name);
		$this->assign('article', $article);
		$this->display();
	}

	public function details()
	{
		$id = getValue('id', 'int');

		if (empty($id)) {
			msg('系统忙碌！', 2, U('index'));
		}

		$article = getData('article', 1, 'id=\'' . $id . '\'');
		
	
		$this->assign('article', $article);
		$tid = $article['type'];
		$type = getData('article_type', 1, 'id=\'' . $tid . '\'');
		$this->assign('type', $type);
		$this->display();
	}
}

?>
