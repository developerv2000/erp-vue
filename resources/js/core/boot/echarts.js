import VChart from 'vue-echarts';
import { use } from 'echarts/core';

// ----- Import only what app uses (tree-shaking) -----

// Chart types
import {
    BarChart,
    LineChart,
    PieChart
} from 'echarts/charts'

// Components
import {
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent
} from 'echarts/components'

// Renderer
import { CanvasRenderer } from 'echarts/renderers'

// Register modules globally
use([
    BarChart,
    LineChart,
    PieChart,
    TitleComponent,
    TooltipComponent,
    LegendComponent,
    GridComponent,
    CanvasRenderer
])

export default VChart
