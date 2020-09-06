const LeagueGroupWarPage = {
    template: `
        <el-tabs v-model="activeName" type="card">
        <el-tab-pane label="联赛" name="second">
        <el-dropdown @command="handleSeasonCommand">
          <span class="el-dropdown-link">
            {{seasonSelected.name}}<i class="el-icon-arrow-down el-icon--right"></i>
          </span>
          <el-dropdown-menu slot="dropdown" >
            <el-dropdown-item icon="el-icon-circle-check" v-for="(option, index) in seasonOptions" :command="index">{{option.season}}</el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
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
                <el-table-column prop="total_star_get" label="得" align="center" sortable></el-table-column>
                <el-table-column prop="total_star_lose" label="丢" align="center" sortable></el-table-column>
                <el-table-column fixed="right" label="净" prop="total_star_gained" sortable> </el-table-column>
            </el-table>
        </el-tab-pane>
    </el-tabs>
    `,
    data: function() {
        return {
            season: "",
            detailData: [],
            activeName: 'second',
            radio: 'w',
            optionSelected: {
                'tag': "",
                'name': "选择部落"
            },
            seasonSelected: {
                'season': "",
                'name': "选择赛季"
            },
            clanOptions: [],
            seasonOptions: [],
        }
    },
    methods: {
        getOption() {
            axios.get('/leagueGroupWarDataClanInfo')
                .then((res) => {
                    let result = res.data;
                    console.log(result);
                    this.clanOptions = result.options;
                    this.seasonOptions = result.seasonOptions;
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
            this.postGetData();
        },
        handleSeasonCommand(index) {
            console.log(index);
            this.seasonSelected = this.seasonOptions[index];
            this.postGetData();
        },
        postGetData() {
            let data = new FormData();
            data.append("tag", this.optionSelected.tag);
            data.append("season", this.seasonSelected.season);
            data.append("league", 1);
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
    },
    created() {
        this.getData();
        this.getOption();
    }
};