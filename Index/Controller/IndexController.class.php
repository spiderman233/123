<?php
namespace index\Controller;

class IndexController extends \Think\Controller
{
	public function _initialize()
	{
		if (isMobile()) {
			header('Location:' . U('mobile/index'));
		}

		if (getInfo('web') == 0) {
		exit();
		}
	}

	public function index()
	{
		
		$this->display();
	}

	public function about()
	{
		$id = getValue('id', 'int');

		if (empty($id)) {
			msg('系统忙碌！', 2, U('index'));
		}

		$data = getData('article_type', 1, 'id=\'' . $id . '\'');
		$this->assign('data', $data);
		$article = getData('article', 'all', 'type=\'' . $id . '\' and `show`=1', '', 'id desc');
		if (count($article) == 0 || 1 < count($article)) {
			$type = 1;
		}
		else {
			$type = 2;
			$article = $article[0];
		}

		$this->assign('article', $article);
		$this->assign('type', $type);
		$this->display('', 'display');
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
