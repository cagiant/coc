const CurrentWarPage = {
    template: `
        <el-tabs v-model="activeName" type="card">
        <el-tab-pane label="战争统计" name="first">
            <el-table :data="summaryData" style="width: 100%;" stripe>
                <el-table-column prop="name" label="昵称" align="center" fixed></el-table-column>
                <el-table-column label="进攻" align="center" width="300">
                    <el-table-column prop="total_star" label="总星" align="center" sortable></el-table-column>
                    <el-table-column prop="avg_attack_star" label="均星" align="center" sortable :filters="[{text: '大于等于2',value: 1},{text: '小于2',value: 2}]" :filter-method="filterAttackStar"></el-table-column>
                    <el-table-column prop="destruction_percentage" label="摧毁率" align="center" sortable></el-table-column>
                </el-table-column>
                <el-table-column label="防守" align="center" width="300">
                    <el-table-column label="总星" prop="defense_total_star" align="center" sortable></el-table-column>
                    <el-table-column label="均星" prop="avg_defense_star" align="center" sortable></el-table-column>
                    <el-table-column label="摧毁率" prop="defense_destruction_percentage" align="center" sortable></el-table-column>
                </el-table-column>
                <el-table-column fixed="right" label="攻防比" prop="adr" sortable align="center" :filters="[{text: '大于等于1',value: 1},{text: '小于1',value: 2}]" :filter-method="filterAdr"></el-table-column>
            </el-table>
        </el-tab-pane>
        <el-tab-pane label="当前战争详情" name="second">
            <div>
                <el-radio v-model="radio" label="w">全部事件</el-radio>    
                <el-radio v-model="radio" label="a">只看进攻</el-radio>
                <el-radio v-model="radio" label="d">只看防守</el-radio>    
            </div>
            <div v-for="(detail, index) in detailData" 
                v-if="(detail.detail_type =='attack' && radio != 'd') || (detail.detail_type == 'defense' && radio != 'a') || (detail.is_zero_attack)">
                <el-divider></el-divider>
                <span v-if="detail.is_zero_attack">
                    <el-tag type="danger">大佬</el-tag>
                </span>
                <span v-if="detail.detail_type =='attack'">
                    <el-tag type="success">事件序号：{{detail.attack_order}}</el-tag>
                </span>
                <span v-if="detail.detail_type =='defense'">
                    <el-tag type="warning">事件序号：{{detail.attack_order}}</el-tag>
                </span>
                <el-divider direction="vertical"></el-divider>
                <span>{{detail.msg}}</span>
            </div>
        </el-tab-pane>
    </el-tabs>
    `,
    data: function() {
        return {
            summaryData: [],
            detailData: [],
            activeName: 'first',
            radio: 'w',
        }
    },
    methods: {
        getData() {
            axios.get('/currentWarData')
                .then((res) => {
                    let result = res.data;
                    console.log(result);
                    if (!result) {
                        this.infoError('获取数据失败，请重试');
                    } else {
                        if (result.code == 1) {
                            this.summaryData = result.data.summary;
                            this.detailData = result.data.detail;
                        } else {
                            this.infoError(result.msg ? result.msg : '获取数据失败，请刷新');
                        }
                    }
                })
        },
        infoError(msg, duration = 3) {
            this.$message.error({
                message: msg,
                duration: duration,
            });
        },
        refreshData() {
            axios.get('/refreshCurrentWarInfo')
                .then((res) => {
                    this.getData();
                    console.log(res.data);
                })
        },
        filterAttackStar(value, row) {
            if (value === 1) {
                return row.avg_attack_star >= 2;
            } else if (value === 2) {
                return row.avg_attack_star < 2;
            }
        },
        filterAdr(value, row) {
            if (value === 1) {
                return row.adr >= 1;
            } else if (value === 2) {
                return row.adr < 1;
            }
        }
    },
    created() {
        this.getData();
        this.refreshData();
    }
};