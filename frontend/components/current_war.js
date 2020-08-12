const CurrentWarPage = {
    template: `
        <el-tabs v-model="activeName" type="card">
        <el-tab-pane :label="season" name="first">
        <el-dropdown @command="handleCommand">
          <span class="el-dropdown-link">
            {{optionSelected.name}}<i class="el-icon-arrow-down el-icon--right"></i>
          </span>
          <el-dropdown-menu slot="dropdown" >
            <el-dropdown-item icon="el-icon-circle-check" v-for="(option, index) in clanOptions" :command="index">{{option.name}}</el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
            <el-table :data="detailData" style="width: 100%;" stripe>
                <el-table-column prop="name" label="昵称" align="center" fixed></el-table-column>
                <el-table-column prop="at_three_star" label="三星次数" align="center" sortable></el-table-column>
                <el-table-column prop="at_two_star" label="二星次数" align="center" sortable></el-table-column>
                <el-table-column prop="at_one_star" label="一星次数" align="center" sortable></el-table-column>
                <el-table-column prop="at_no_star" label="零星次数" align="center" sortable></el-table-column>
                <el-table-column prop="df_three_star" label="被三次数" align="center" sortable></el-table-column>
                <el-table-column prop="at_time_left" label="未使用进攻次数" align="center" sortable></el-table-column>
                <el-table-column fixed="right" label="已使用进攻次数" prop="at_time_used"> sortable</el-table-column>
            </el-table>
        </el-tab-pane>
<!--        <el-tab-pane label="当前战争详情" name="second">-->
<!--            <div>-->
<!--                <el-radio v-model="radio" label="w">全部事件</el-radio>    -->
<!--                <el-radio v-model="radio" label="a">只看进攻</el-radio>-->
<!--                <el-radio v-model="radio" label="d">只看防守</el-radio>    -->
<!--            </div>-->
<!--            <div v-for="(detail, index) in detailData" -->
<!--                v-if="(detail.detail_type =='attack' && radio != 'd') || (detail.detail_type == 'defense' && radio != 'a') || (detail.is_zero_attack)">-->
<!--                <el-divider></el-divider>-->
<!--                <span v-if="detail.is_zero_attack">-->
<!--                    <el-tag type="danger">大佬</el-tag>-->
<!--                </span>-->
<!--                <span v-if="detail.detail_type =='attack'">-->
<!--                    <el-tag type="success">事件序号：{{detail.attack_order}}</el-tag>-->
<!--                </span>-->
<!--                <span v-if="detail.detail_type =='defense'">-->
<!--                    <el-tag type="warning">事件序号：{{detail.attack_order}}</el-tag>-->
<!--                </span>-->
<!--                <el-divider direction="vertical"></el-divider>-->
<!--                <span>{{detail.msg}}</span>-->
<!--            </div>-->
<!--        </el-tab-pane>-->
    </el-tabs>
    `,
    data: function() {
        return {
            season: "",
            detailData: [],
            activeName: 'first',
            radio: 'w',
            optionSelected: {
                'tag': "",
                'name': "选择部落"
            },
            clanOptions: [],
        }
    },
    methods: {
        getOption() {
            axios.get('/currentWarDataClanInfo')
                .then((res) => {
                    let result = res.data;
                    console.log(result);
                    this.clanOptions = result.options;
                })
        },
        getData() {
            axios.get('/currentWarData')
                .then((res) => {
                    let result = res.data;
                    console.log(result);
                    this.season = result.season;
                    this.detailData = result.detail;
                    console.log(this.season);
                    console.log(this.detailData);
                })
        },
        handleCommand(index) {
            console.log(index);
            this.optionSelected = this.clanOptions[index];
            let data = new FormData();
            data.append("tag", this.optionSelected.tag);
            axios.post('/currentWarData', data)
                .then((res) => {
                    let result = res.data;
                    console.log(result);
                    this.season = result.season;
                    this.detailData = result.detail;
                    console.log(this.season);
                    console.log(this.detailData);
                })
        },
        infoError(msg, duration = 3) {
            this.$message.error({
                message: msg,
                duration: duration,
            });
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
        this.getOption();
    }
};