import React from "react";
import { useQuery } from "@tanstack/react-query";
import { fetchData } from "./dashboard_utils";
import { HealthResponse } from "typesense/lib/Typesense/Health";
import { MetricsResponse } from "typesense/lib/Typesense/Metrics";
import { amber, grey, lightGreen, red } from "@mui/material/colors";
import Box from "@mui/material/Box";
import CircularProgress from "@mui/material/CircularProgress";
import Stack from "@mui/material/Stack";
import LinearProgress from "@mui/material/LinearProgress";
import Card from "@mui/material/Card";
import CardHeader from "@mui/material/CardHeader";
import StorageIcon from "@mui/icons-material/Storage";
import CardContent from "@mui/material/CardContent";

declare const typesenseI18n: { [key: string]: string };

export default function CardCollections () {
    const collectionsQuery = useQuery({
        queryKey: ['health'],
        queryFn: async () => fetchData<HealthResponse>('getCollections', false),
        retry: false,
    });

    let cardContent: JSX.Element =
        <Box textAlign="center">
            <CircularProgress sx={{my: 2}}/>
        </Box>;
    let headerBgColor = grey[300] as string;

    if (!collectionsQuery.isLoading) {
        if (collectionsQuery.isError || !collectionsQuery.data) {
            console.log(collectionsQuery.error);
            headerBgColor = amber[300];
            cardContent =
                <Box textAlign="center">
                    <p><strong>{typesenseI18n['TYPESENSE_DASHBOARD_AJAX_ERROR_TEXT_1']}</strong></p>
                    <p>{typesenseI18n['TYPESENSE_DASHBOARD_AJAX_ERROR_TEXT_2']}</p>
                </Box>
        } else {
            console.log(metricsQuery.data);
            headerBgColor = collectionsQuery.data.ok ? lightGreen[300] : red[300];
            const diskUsedGb = convertToGB(metricsQuery.data.system_disk_used_bytes);
            const diskTotalGb = convertToGB(metricsQuery.data.system_disk_total_bytes);
            const diskUsedPercent = Math.round(diskUsedGb / diskTotalGb * 100);
            const memoryUsedGb = convertToGB(metricsQuery.data.system_memory_used_bytes);
            const memoryTotalGb = convertToGB(metricsQuery.data.system_memory_total_bytes);
            const memoryUsedPercent = Math.round(memoryUsedGb / memoryTotalGb * 100);

            cardContent =
                <Stack>
                    <Box pb={1}>
                        <strong>{collectionsQuery.data.ok
                            ? typesenseI18n['TYPESENSE_DASHBOARD_CARD_STATUS_OK']
                            : typesenseI18n['TYPESENSE_DASHBOARD_CARD_STATUS_ERROR']
                        }</strong>
                    </Box>
                    <Box mt={2} fontSize='0.8em'>
                        Memory usage: {memoryUsedPercent}%
                    </Box>
                    <Box sx={{display: 'flex', alignItems: 'center', columnGap: 2, marginTop: '0.1em'}}>
                        <Box sx={{width: '75%'}}>
                            <LinearProgress variant="determinate" value={memoryUsedPercent} sx={{height: 12}}/>
                        </Box>
                        <Box sx={{fontSize: '0.8em'}}>
                            {memoryUsedGb.toFixed(2)} / {memoryTotalGb.toFixed(2)} GB
                        </Box>
                    </Box>
                    <Box mt={1} fontSize='0.8em'>
                        Disk usage: {diskUsedPercent}%
                    </Box>
                    <Box sx={{display: 'flex', alignItems: 'center', columnGap: 2, marginTop: '0.1em'}}>
                        <Box sx={{width: '75%'}}>
                            <LinearProgress variant="determinate" value={diskUsedPercent} sx={{height: 12}}/>
                        </Box>
                        <Box sx={{fontSize: '0.8em'}}>
                            {diskUsedGb.toFixed(2)} / {diskTotalGb.toFixed(2)} GB
                        </Box>
                    </Box>
                </Stack>;
        }
    }

    return (
        <Card>
            <CardHeader
                title={
                    <Stack direction="row" alignItems="center" gap={1}>
                        <StorageIcon/> {typesenseI18n['TYPESENSE_DASHBOARD_CARD_STATUS_TITLE']}
                    </Stack>
                }
                titleTypographyProps={{align: 'center'}}
                sx={{backgroundColor: headerBgColor}}
            />
            <CardContent>
                {cardContent}
            </CardContent>
        </Card>
    );
}
