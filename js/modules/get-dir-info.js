import createDirBtn from "./create-dir-btn.js";
import { $mainList, $mainSection } from "../main.js";
import createFileBtn from "./create-file-btn.js";

export default async function getDirInfo(id) {
    const request = await fetch(`http://www.cloud-storage.local/directory/${id}`);
    const data = await request.json();

    if (!data.status) {
        return;
    }

    // создание списка файлов и папок;
    const dirArray = data.directories_list;
    const fileArray = data.files_list;
    
    for (const dir of dirArray) {
        const $dir = createDirBtn(dir);

        $mainList.append($dir);
    }

    for (const file of fileArray) {
        const $file = await createFileBtn(file);

        $mainList.append($file);
    }

    $mainSection.append($mainList);

    return $mainList;
}