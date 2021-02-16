<?php


namespace Tests\Unit;


use App\Database\PDODatabaseConnection;
use App\Database\PDOQueryBuilder;
use App\Helpers\Config;
use PHPUnit\Framework\TestCase;

class PDOQueryBuilderTest extends TestCase
{

    private $pdoQueryBuilder;

    public function setUp(): void
    {
        $pdoDatabaseConnection = new PDODatabaseConnection($this->getConfigs());

        $this->pdoQueryBuilder = new PDOQueryBuilder($pdoDatabaseConnection->connect());

        $this->pdoQueryBuilder->beginTransaction();
        parent::setUp();
    }

    public function testInsertValueToTable()
    {
        $response = $this->insertDataToTable();
        $this->assertIsInt($response);
        $this->assertGreaterThan(0, $response);
    }

    public function testUpdateValueInTableWithWhereOR()
    {
        $this->insertDataToTable();
        $response = $this->pdoQueryBuilder->table('users')
            ->where(["OR"=>['name' => 'Amin','OR'=>['Family' => 'Rahimzadeh','Instagram' => 'amin.rz3']]])
            ->update(['name' => 'Hossein', 'Family' => 'Hasani']);
        $this->assertIsInt($response);
        $this->assertEquals(1, $response);
    }

    public function testUpdateValueInTableWithWhereAND()
    {
        $this->insertDataToTable();
        $response = $this->pdoQueryBuilder->table('users')
            ->where(["AND"=>['name' => 'Amin','AND'=>['Family' => 'Rahimzadeh','Instagram' => 'amin.rz3']]])
            ->update(['name' => 'Hossein', 'Family' => 'Hasani']);
        $this->assertIsInt($response);
        $this->assertEquals(1, $response);
    }

    public function testUpdateValueInTableWithWhereOperatorNot()
    {
        $this->insertDataToTable();
        $response = $this->pdoQueryBuilder->table('users')
            ->where(["name[!]"=>"A"])
            ->update(['name' => 'Hossein', 'Family' => 'Hasani']);
        $this->assertIsInt($response);
        $this->assertEquals(1, $response);
    }

    public function testUpdateValueInTable()
    {
        $this->insertDataToTable();
        $response = $this->pdoQueryBuilder->table('users')
            ->where(['name' => 'Amin', 'Family' => 'Rahimzadeh'])
            ->update(['name' => 'Ali', 'Family' => 'Hasani']);

        $this->assertIsInt($response);
        $this->assertEquals(1, $response);
    }

    public function testNotUpdatesValueInTable(){
        $this->insertDataToTable();
        $response = $this->pdoQueryBuilder->table('users')
            ->where(['name' => 'A', 'Family' => 'Rahimzadeh'])
            ->update(['name' => 'Ali', 'Family' => 'Hasani']);

        $this->assertIsInt($response);
        $this->assertEquals(0, $response);
    }

    public function testUpdateValueInTableWithoutWhere(){
        $this->insertDataToTable();
        $response = $this->pdoQueryBuilder->table('users')
            ->update(['name' => 'Ali', 'Family' => 'Hasani']);

        $this->assertIsInt($response);
        $this->assertEquals(1, $response);
    }

    public function testWhereUpdate()
    {
        $this->insertDataToTable();
        $this->insertDataToTable(['Family' => 'Hosseini']);

        $response = $this->pdoQueryBuilder->table('users')
            ->where(['Family' => 'Hosseini'])
            ->update(['name' => 'Ali', 'Family' => 'Hasani']);

        $this->assertIsInt($response);
        $this->assertEquals(1, $response);
    }

    public function testDeleteValueTable()
    {

        $this->insertDataToTable();

        $response = $this->pdoQueryBuilder->table('users')
            ->where(['name' => 'Amin', 'Family' => 'Rahimzadeh'])
            ->delete();

        $this->assertEquals(1, $response);
    }

    public function testGetDataFromTable(){
        $this->multipleInsertData(10, ['Instagram'=>'Heydar']);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['Instagram'=>'Heydar'])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(10,$result);
    }

    public function testGetDataFromTableReturnNotRecord(){
        $this->multipleInsertData(10, ['Instagram'=>'Heydar']);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['Instagram'=>'H'])
            ->get();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetDataFromTableWithOrderBy(){
        $this->multipleInsertData(10, ['Instagram'=>'Heydar']);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['ORDER'=>['id'=>'DESC']])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(10,$result);
    }

    public function testGetDataFromTableWithOrderByField(){
        $this->multipleInsertData(10, ['Instagram'=>'Heydar']);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['ORDER'=>['id'=>[1,2,3,4,5]]])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(10,$result);
    }

    public function testGetDataFromTableWithMatchNatural(){
        $this->insertDataToTable();

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['MATCH'=>[
                'columns'=>['name'],
                'key'=>'Amin',
                'option'=>'natural'
            ]])
            ->get();

        $this->assertIsArray($result);
       // $this->assertCount(1,$result);
    }

    public function testGetDataFromTableWithGROUPBySingle(){
        $this->insertDataToTable();

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['GROUP'=>'name'])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(1,$result);
    }

    public function testGetDataFromTableWithGROUPByArray(){
        $this->insertDataToTable();

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['GROUP'=>['name','Family']])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(1,$result);
    }

    public function testGetDataFromTableWithLimitSingle(){
        $this->multipleInsertData(10);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['LIMIT'=>5])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(5,$result);
    }


    public function testGetDataFromTableWithLimitArray(){
        $this->multipleInsertData(10);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['LIMIT'=>[0,8]])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(8,$result);
    }


    public function testGetDataFromTableWithLIKE(){
        $this->insertDataToTable();

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['name[~]'=>'Am','Family[~]'=>'Rahim'])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(1,$result);
    }

    public function testGetDataFromTableWithLIKEArray(){
        $this->insertDataToTable();

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['name[~]'=>['Am','Ami']])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(1,$result);
    }


    public function testGetDataFromTableWithLIKEArrayWithAND(){
        $this->insertDataToTable();

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['name[~]'=>['AND'=>['Am','Ami']]])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(1,$result);
    }

    public function testGetDataFromTableWithLIKEArrayWithOR(){
        $this->insertDataToTable();

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['name[~]'=>['OR'=>['Am','Ami']]])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(1,$result);
    }

    public function testGetDataFromTableWithLikeAndOrderByAndLimit(){
        $this->multipleInsertData(10);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['name[~]'=>['Am','Ami'],'ORDER'=>['id'=>'DESC'],'LIMIT'=>[0,5]])
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(5,$result);
    }


    public function testGetDataFromTableNoWhere(){
        $this->multipleInsertData(10);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->get();

        $this->assertIsArray($result);
        $this->assertCount(10,$result);
    }

    public function testGetDataFromTableWithColumnName(){
        $this->multipleInsertData(10);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['Instagram'=>'amin.rz3'])
            ->get(['name','Family']);

        $obj = json_decode(json_encode($result), FALSE);
        $this->assertIsArray($result);
        $this->assertObjectHasAttribute('name',$obj[0]);
        $this->assertObjectHasAttribute('Family',$obj[0]);

        $this->assertEquals(['name','Family'], array_keys($result[0]));
    }

    public function testGetDataFromTableWithColumnNameNoWhere(){
        $this->multipleInsertData(10);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->get(['name','Family']);

        $obj = json_decode(json_encode($result), FALSE);
        $this->assertIsArray($result);
        $this->assertObjectHasAttribute('name',$obj[0]);
        $this->assertObjectHasAttribute('Family',$obj[0]);

        $this->assertEquals(['name','Family'], array_keys($result[0]));
    }

    public function testGetFirstRow(){
        $this->multipleInsertData(10, ['Instagram'=>'Amin']);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['Instagram'=>'Amin'])
            ->first();

        $obj = json_decode(json_encode($result), FALSE);
        $this->assertIsObject($obj);
        $this->assertObjectHasAttribute('name',$obj);
        $this->assertObjectHasAttribute('Family',$obj);
        $this->assertObjectHasAttribute('Instagram',$obj);
        $this->assertObjectHasAttribute('Job',$obj);

    }

    public function testGetFirstRowReturnNull(){
        $this->multipleInsertData(10, ['Instagram'=>'Amin']);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->where(['Instagram'=>'A'])
            ->first();

        $this->assertNull($result);
    }

    public function testFindFromTable(){
        $id = $this->insertDataToTable(['name'=>'FIND AMIN']);

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->find($id);

        $this->assertIsArray($result);
        $this->assertEquals("FIND AMIN",$result['name']);
    }

    public function testFindbyFromTable(){
        $id = $this->insertDataToTable();

        $result = $this->pdoQueryBuilder
            ->table('users')
            ->findby(['name'=>'Amin']);

        $this->assertIsArray($result);
        $this->assertEquals($id,$result[0]['id']);
    }

    private function multipleInsertData($num, $options=[]){
        for ($i=0; $i<$num; $i++){
            $this->insertDataToTable($options);
        }
    }

    private function insertDataToTable($options = [])
    {
        $data = array_merge([
            'name' => 'Amin',
            'Family' => 'Rahimzadeh',
            'Instagram' => 'amin.rz3',
            'Job' => 'Android and Php Developer',
        ], $options);

        return $this->pdoQueryBuilder->table('users')->insert($data);
    }

    private function getConfigs()
    {
        $configs = Config::get('database', 'pdo_testing');
        return $configs;
    }

    public function tearDown(): void
    {
        $this->pdoQueryBuilder->rollback();
        parent::tearDown();
    }
}