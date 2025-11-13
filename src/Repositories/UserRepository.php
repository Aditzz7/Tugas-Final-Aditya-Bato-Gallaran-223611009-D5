<?php
namespace Src\Repositories;

use PDO;
use Src\Config\Database;

class UserRepository{
    private PDO $db;
    public function __construct(array $cfg){
        $this->db=Database::conn($cfg);
    }
    public function paginate($page,$per,$search='',$sort='id',$direction='DESC'){
        $off=($page-1)*$per;
        $whereClause='';
        $params=[];
        if(!empty($search)){
            $whereClause='WHERE name LIKE :search OR email LIKE :search';
            $params[':search']='%'.$search.'%';
        }
        $allowedSortFields=['id','name','email'];
        if(!in_array($sort,$allowedSortFields)){
            $sort='id';
        }
        $direction=strtoupper($direction);
        if(!in_array($direction,['ASC','DESC'])){
            $direction='DESC';
        }
        $totalQuery='SELECT COUNT(*) FROM users '.$whereClause;
        $totalStmt=$this->db->prepare($totalQuery);
        foreach($params as $key=>$value){
            $totalStmt->bindValue($key,$value);
        }
        $totalStmt->execute();
        $total=(int)$totalStmt->fetchColumn();
        $query='SELECT id, name, email FROM users '.$whereClause.' ORDER BY '.$sort.' '.$direction.' LIMIT :per OFFSET :off';
        $stmt=$this->db->prepare($query);
        foreach($params as $key=>$value){
            $stmt->bindValue($key,$value);
        }
        $stmt->bindValue(':per',(int)$per,PDO::PARAM_INT);
        $stmt->bindValue(':off',(int)$off,PDO::PARAM_INT);
        $stmt->execute();
        return[
            'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC),
            'meta'=>[
                'total'=>$total,
                'page'=>$page,
                'per_page'=>$per,
                'last_page'=>max(1,(int)ceil($total/$per))
            ]
        ];
    }

    public function find($id){
        $s=$this->db->prepare('SELECT id, name, email FROM users WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }
    public function create($name,$email,$hash){
        $this->db->beginTransaction();
        try{
            $s=$this->db->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $s->execute([$name,$email,$hash]);
            $id=(int)$this->db->lastInsertId();
            $this->db->commit();
            return $this->find($id);
        }catch(\Throwable $e){
            $this->db->rollBack();
            throw $e;
        }
    }
    public function update($id,$name,$email,$passwordHash=null){
        if($passwordHash){
            $s=$this->db->prepare('UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?');
            $s->execute([$name,$email,$passwordHash,$id]);
        }else{
            $s=$this->db->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            $s->execute([$name,$email,$id]);
        }
        return $this->find($id);
    }
    public function delete($id){
        $s=$this->db->prepare('DELETE FROM users WHERE id = ?');
        return $s->execute([$id]);
    }
}
