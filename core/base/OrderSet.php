<?php 

namespace core\base;

class OrderSet {
    public ?Node $head = null;
    private array $array = [];

    public function add (int $data): void  
    {
        $node = new Node($data);
        
        if($this->head == null){
            $this->head = $node;
            return;
        }

        $this->addToNode($node, $this->head);
    }

    private function addToNode (Node $node, Node $head): void 
    {
        while(true) {
            if($node->data == $head->data) break;
            if($node->data < $head->data){
                if($head->left == null) {
                    $head->left = $node;
                    break;
                }
                $head = $head->left;
            }

            if($node->data > $head->data){
                if($head->right == null) {
                    
                    $head->right = $node;
                    break;
                }
                $head = $head->right;
            }
        }
    }

    public function join ():string
    {
        if($this->head == null) return '';
        $result = '';
        $this->joinNode($result, $this->head);
        return trim($result, ',');
    }

    private function joinNode (string &$result, Node $node) 
    {
        if($node != null) {
            if($node->left) $this->joinNode($result,$node->left);
            $result .= $node->data.',';
            if($node->right) $this->joinNode($result,$node->right);
        }
    }
}